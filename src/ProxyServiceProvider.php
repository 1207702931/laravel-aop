<?php

namespace Wentaophp\Proxy;

use App\Util\ResponseStruct;
use Composer\ClassMapGenerator\PhpFileParser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Wentaophp\Proxy\Aop\Annotation\AnnotationInterface;
use Wentaophp\Proxy\Aop\Annotation\AnnotationReader;
use Wentaophp\Proxy\Aop\Annotation\Aspect;
use Wentaophp\Proxy\Aop\Annotation\AspectLoader;
use Wentaophp\Proxy\Aop\AspectManager;
use Wentaophp\Proxy\Aop\Ast;
use Wentaophp\Proxy\Aop\AstVisitorRegistry;
use Wentaophp\Proxy\Aop\Collector\AnnotationCollector;
use Wentaophp\Proxy\Aop\Collector\AspectCollector;
use Wentaophp\Proxy\Aop\Composer\ClassLoader;
use Wentaophp\Proxy\Aop\ProxyCallVisitor;
use Wentaophp\Proxy\Aop\ReflectionManager;

class ProxyServiceProvider extends ServiceProvider
{

    private const string CACHE_PROXY_SERVICE_PROVIDER = 'CACHE_PROXY_SERVICE_PROVIDER_';
    private const string ANNOTATION_COLLECTOR = self::CACHE_PROXY_SERVICE_PROVIDER . 'AnnotationCollector';
    private const string ASPECT_COLLECTOR_CONTAINER = self::CACHE_PROXY_SERVICE_PROVIDER . 'AspectCollector::Container';
    private const string ASPECT_COLLECTOR_ASPECT_RULES = self::CACHE_PROXY_SERVICE_PROVIDER . 'AspectCollector::AspectRules';
    private const string ASPECT_MANAGER = self::CACHE_PROXY_SERVICE_PROVIDER . 'AspectManager';
    private const string CLASSMAP = self::CACHE_PROXY_SERVICE_PROVIDER . 'ClassMap';

    // 注册 classLoader 到容器方便获取，修改
    public function register(): void
    {
        $this->app->bind('ProxyServiceProviderClassLoader', fn() => ClassLoader::findClassLoader());
    }

    /**
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole() &&
            in_array(request()->server('argv')[1] ?? '', [
                'octane:start', 'octane:reload'
            ])) {
            Cache::driver('file')->delete(self::CACHE_PROXY_SERVICE_PROVIDER);
        }
        $classMap = $this->loadAnnotationAndAspect();
        app('ProxyServiceProviderClassLoader')->addClassMap($classMap);
    }

    /**
     * @return array
     */
    private function loadClassMap(): array
    {
        $cachedProxyFiles = File::allFiles(storage_path('proxy'));
        $classMap = [];
        foreach ($cachedProxyFiles as $cachedProxyFile) {
            $classes = PhpFileParser::findClasses($cachedProxyFile);
            foreach ($classes as $class) {
                $classMap[$class] = $cachedProxyFile->getPathname();
            }
        }
        $classMap[ResponseStruct::class] = storage_path('app/ResponseStruct.php');
        return $classMap;
    }

    /**
     * 缓存注解, 以及切面相关数据
     * @return array
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function loadAnnotationAndAspect(): array
    {
        if (Cache::driver('file')->get(self::CACHE_PROXY_SERVICE_PROVIDER)) {
            return $this->loadFormCache();
        }
        AstVisitorRegistry::insert(ProxyCallVisitor::class);

        $cachePath = storage_path('proxy/');
        File::cleanDirectory($cachePath);
        $proxyPath = app_path();

        File::makeDirectory($cachePath, 0777, true, true);
        $reader = app(AnnotationReader::class);
        $classes = ReflectionManager::getAllClasses([$proxyPath]);
        foreach ($classes as $className => $reflectionClass) {
            $this->collectAnnotations($reader, $reflectionClass, $className);
        }

        // 有注解的类
        foreach (AnnotationCollector::getContainer() as $class => $value) {
            $code = app(Ast::class)->proxy($class);
            File::put($cachePath . str_replace('\\', '_', $class . '.php'), $code);
        }

        return $this->setCache();
    }

    /**
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function loadFormCache(): array
    {
        AnnotationCollector::setContainer(Cache::driver('file')->get(self::ANNOTATION_COLLECTOR));
        AspectCollector::setContainer(Cache::driver('file')->get(self::ASPECT_COLLECTOR_CONTAINER));
        AspectCollector::setAspectRules(Cache::driver('file')->get(self::ASPECT_COLLECTOR_ASPECT_RULES));
        AspectManager::setContainer(Cache::driver('file')->get(self::ASPECT_MANAGER));
        return Cache::driver('file')->get(self::CLASSMAP);
    }

    /**
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function setCache(): array
    {
        Cache::driver('file')->set(self::CACHE_PROXY_SERVICE_PROVIDER, 1);
        Cache::driver('file')->set(self::ANNOTATION_COLLECTOR, AnnotationCollector::getContainer());
        Cache::driver('file')->set(self::ASPECT_COLLECTOR_CONTAINER, AspectCollector::getContainer());
        Cache::driver('file')->set(self::ASPECT_COLLECTOR_ASPECT_RULES, AspectCollector::getAspectRules());
        Cache::driver('file')->set(self::ASPECT_MANAGER, AspectManager::getContainer());
        $classMap = $this->loadClassMap();
        Cache::driver('file')->set(self::CLASSMAP, $classMap);
        return $classMap;
    }

    /**
     * 收集可用的注解
     * @param AnnotationReader $reader
     * @param $reflection
     * @param $className
     * @return void
     * @throws \Exception
     */
    public function collectAnnotations(AnnotationReader $reader, $reflection, $className): void
    {
        $classAnnotations = $reader->getClassAnnotations($reflection);
        // 分析类批注
        if (!empty($classAnnotations)) {
            foreach ($classAnnotations as $classAnnotation) {
                if ($classAnnotation instanceof AnnotationInterface) {
                    $classAnnotation->collectClass($className);
                }

                if ($classAnnotation instanceof Aspect) {
                    $this->loadAspect($className, $classAnnotation->priority);
                }
            }
        }
        // 分析属性批注
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            $propertyAnnotations = $reader->getPropertyAnnotations($property);
            if (!empty($propertyAnnotations)) {
                foreach ($propertyAnnotations as $propertyAnnotation) {
                    if ($propertyAnnotation instanceof AnnotationInterface) {
                        $propertyAnnotation->collectProperty($className, $property->getName());
                    }
                }
            }
        }
        // 分析方法批注
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            $methodAnnotations = $reader->getMethodAnnotations($method);
            if (!empty($methodAnnotations)) {
                foreach ($methodAnnotations as $methodAnnotation) {
                    if ($methodAnnotation instanceof AnnotationInterface) {
                        $methodAnnotation->collectMethod($className, $method->getName());
                    }
                }
            }
        }
        unset($reflection, $classAnnotations, $properties, $methods);
    }

    public function loadAspect($aspect, $value)
    {
        $priority = (int)$value;


        [$instanceClasses, $instanceAnnotations, $instancePriority] = AspectLoader::load($aspect);

        $classes = $instanceClasses ?: [];
        // Annotations
        $annotations = $instanceAnnotations ?: [];
        // Priority
        $priority = $priority ?: ($instancePriority ?? null);
        // Save the metadata to AspectCollector
        AspectCollector::setAround($aspect, $classes, $annotations, $priority);

    }
}

### 抄袭 hyperf 框架AOP部分功能，实现了一个简单的AOP框架，支持方法前置、后置、环绕，支持注解和切面类两种方式配置切面。

1. 支持fpm 运行环境，和 octane(swoole) 运行环境。
2. fpm 环境下在没有缓存时需要执行命令

 ```shell
php artisan proxy:clear-cache
```

3. octane 环境下每次重启服务会自动重载缓存。

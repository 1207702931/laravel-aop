<?php

namespace Wentaophp\Proxy\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Wentaophp\Proxy\ProxyServiceProvider;

class ProxyClearCacheCommand extends Command
{
    protected $signature = 'proxy:clear-cache';

    protected $description = '清理代理缓存';

    /**
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(): void
    {
        Cache::driver('file')->delete(ProxyServiceProvider::CACHE_PROXY_SERVICE_PROVIDER);
        $this->line('代理缓存清理成功');
        app(ProxyServiceProvider::class, ['app' => app()])->forceLoadCache()->boot();
        if (Cache::driver('file')->has(ProxyServiceProvider::CACHE_PROXY_SERVICE_PROVIDER)) {
            $this->line('代理缓存加载成功');
        } else {
            $this->line('代理缓存加载失败');
        }
    }
}

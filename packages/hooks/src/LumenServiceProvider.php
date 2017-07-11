<?php

namespace PHPNa\Hooks;

use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPNa\Hooks\Contracts\LoggerInterface;
use PHPNa\Hooks\Exceptions\DeployErrorException;
use PHPNa\Hooks\Listeners\DeployListener;
use PHPNa\Hooks\Middleware\HooksMiddleware;
use PHPNa\Hooks\Repository\SiteManager;

class LumenServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerConfig();
        $this->registerQueue();
        $this->registerMiddleware();
        if (!$this->app['config']->get('hooks.single')){
            $this->registerRoute();
        }else{
            $this->registerSingleRoute();
        }
    }

    public function register()
    {
        $this->app->singleton(LoggerInterface::class, function () {
            $handler = (new StreamHandler(storage_path('logs/hooks.log'), Logger::DEBUG))
                ->setFormatter(new LineFormatter(null, null, true, true));
            return new Logger('hooks', [$handler]);
        });
        $this->app->alias(LoggerInterface::class, 'hooks.log');
        $this->app->singleton(Hooks::class, function ($app) {
            try {
                return new Hooks($app, new SiteManager($app['config']));
            } catch (DeployErrorException $e) {
                $this->app['hooks.log']->error($e->errorMessage());
            }
        });
        $this->app->alias(Hooks::class, 'hooks');
    }

    protected function registerMiddleware()
    {
        $this->app->routeMiddleware([
            'hooks' => HooksMiddleware::class
        ]);
    }

    protected function registerRoute()
    {
        $route = $this->app['config']->get('hooks.route');
        $default_site = $this->app['hooks']->siteManager()->getDefault();
        $this->app->get("{$route['prefix']}[/{site}]",
            [
                'middleware' => 'hooks',
                function ($site = null) use ($default_site) {
                    $site = $site ?: $default_site['name'];
                    event('hooks.deploy', ['site' => $site]);
                }
            ]
        );
    }

    protected function registerSingleRoute()
    {
        $route = $this->app['config']->get('hooks.route');
        $this->app->get($route['prefix'], function () {
            event('hooks.deploy');
        });
    }

    protected function registerQueue()
    {
        if (!$this->app['config']->get('database')) {
            $this->app->configure('database');
        }
        if (!$this->app['config']->get('queue')) {
            $this->app->configure('queue');
        }
        $queue_config = $this->app['config']->get('hooks.queue');
        if ($queue_config['driver'] == 'database' && !empty($queue_config['connection'])) {
            $this->app['config']->set('database.connections.hooks', $queue_config['connection']);
            array_pull($queue_config, 'connection');
            $queue_config['connection'] = 'hooks';
        }
        $this->app['config']->set('queue.connections.hooks', $queue_config);
        $this->app['events']->listen('hooks.deploy', DeployListener::class);
    }

    protected function registerConfig()
    {
        if (!$this->app['config']->get('hooks')) {
            $this->app->configure('hooks');
        }
        $config_path = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($config_path, 'hooks');
    }
}

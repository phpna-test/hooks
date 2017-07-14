<?php
namespace Gkr\Hooks\Providers;
use Gkr\Hooks\Commands\DeleteCommand;
use Gkr\Hooks\Commands\MakeCommand;
use Gkr\Hooks\Commands\ResetCommand;
use Gkr\Hooks\Commands\ShowCommand;
use Gkr\Hooks\Deploy\Logger;
use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Gkr\Hooks\Contracts\HooksInterface;
use Gkr\Hooks\Contracts\LoggerInterface;
use Gkr\Hooks\Deploy\ErrorException;
use Gkr\Hooks\Deploy\RequestMiddleware;
use Gkr\Hooks\Hooks;
use Gkr\Hooks\Deploy\EventListener;
use Gkr\Hooks\Repository\SiteManager;
abstract class BaseServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerQueue();
        $this->registerMiddleware();
        if (!$this->app['config']->get('hooks.single')){
            $this->registerRoute();
            $this->registerCommands();
        }else{
            $this->registerSingleRoute();
        }
    }
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton(LoggerInterface::class, function () {
            $handler = (new StreamHandler(storage_path('logs/hooks.log'), Logger::DEBUG))
                ->setFormatter(new LineFormatter(null, null, true, true));
            return new Logger('hooks', [$handler]);
        });
        $this->app->alias(Logger::class, 'hooks.log');
        $this->app->alias(LoggerInterface::class, 'hooks.log');
        $this->app->singleton(HooksInterface::class, function ($app) {
            try {
                return new Hooks($app, new SiteManager($app['config']));
            } catch (ErrorException $e) {
                $this->app['hooks.log']->error($e->errorMessage());
            }
        });
        $this->app->alias(HooksInterface::class, Hooks::class);
        $this->app->alias(HooksInterface::class, 'hooks');
    }
    /**
     * Register the middleware for web hooks.
     */
    protected function registerMiddleware()
    {
        $this->app->routeMiddleware([
            'hooks' => RequestMiddleware::class
        ]);
    }

    /**
     * Register the Queue event for hooks deploy.
     * Queue of hooks use its owner config.
     * Please config it in config/hooks.php instead of config/queue.php
     */
    protected function registerQueue()
    {
        $queue_config = $this->app['config']->get('hooks.queue');
        if ($queue_config['driver'] == 'database' && !empty($queue_config['connection'])) {
            $this->app['config']->set('database.connections.hooks', $queue_config['connection']);
            array_pull($queue_config, 'connection');
            $queue_config['connection'] = 'hooks';
        }
        array_pull($queue_config,'timeout');
        array_pull($queue_config,'tries');
        $queue_config['queue'] = 'default';
        $this->app['config']->set('queue.connections.hooks', $queue_config);
        $this->app['events']->listen('hooks.deploy', EventListener::class);
    }

    protected function registerCommands()
    {
        $this->commands([
            MakeCommand::class,
            ShowCommand::class,
            DeleteCommand::class,
            ResetCommand::class
        ]);
    }

    /**
     * Register the config of hooks for lumen or laravel framework.
     * @return mixed
     */
    abstract protected function registerConfig();
    /**
     * Register the multiple sites model route for lumen or laravel framework.
     * @return mixed
     */
    abstract protected function registerRoute();
    /**
     * Register the current site model route for lumen or laravel framework.
     * @return mixed
     */
    abstract protected function registerSingleRoute();
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['hooks'];
    }
}
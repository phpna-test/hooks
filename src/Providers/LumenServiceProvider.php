<?php
namespace Gkr\Hooks\Providers;

use Illuminate\Http\Request;

class LumenServiceProvider extends BaseServiceProvider
{
    /**
     * Register the hooks route for lumen framework
     * This route for multiple sites when hooks.single config been false or none
     */
    protected function registerRoute()
    {
        $route = $this->app['config']->get('hooks.route');
        $default_site = $this->app['hooks']->manager()->getDefault();
        $this->app->post("{$route['prefix']}[/{site}]",
            [
//                'middleware' => 'hooks',
                function ($site = null,Request $request) use ($default_site) {
                    $site = $site ?: $default_site['name'];
                    event('hooks.deploy', ['client' => $request->all(),'site' => $site]);
                }
            ]
        );
    }

    /**
     * Register the hooks route for lumen framework
     * This route for current app when hooks.single config been true
     */
    protected function registerSingleRoute()
    {
        $route = $this->app['config']->get('hooks.route');
        $this->app->post($route['prefix'], function (Request $request) {
            event('hooks.deploy',['client' => $request->all()]);
        });
    }

    /**
     * Register the hooks config for lumen framework
     */
    protected function registerConfig()
    {
        if (!$this->app['config']->get('database')) {
            $this->app->configure('database');
        }
        if (!$this->app['config']->get('queue')) {
            $this->app->configure('queue');
        }
        if (!$this->app['config']->get('hooks')) {
            $this->app->configure('hooks');
        }
        $config_path = __DIR__ . '/../../config.php';
        $this->mergeConfigFrom($config_path, 'hooks');
    }
}
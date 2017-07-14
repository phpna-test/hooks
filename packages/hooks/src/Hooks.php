<?php

namespace Gkr\Hooks;

use Illuminate\Container\Container;
use Gkr\Hooks\Contracts\HooksInterface;
use Gkr\Hooks\Deploy\ErrorException;
use Gkr\Hooks\Deploy\Process;
use Gkr\Hooks\Repository\SiteConfigTrait;
use Gkr\Hooks\Repository\SiteManager;

/**
 * Hooks bootstrap class
 * @package Gkr\Hooks
 */
class Hooks implements HooksInterface
{
    use SiteConfigTrait;
    /**
     * The laravel application instance.
     * @var Container
     */
    protected $app;
    /**
     * The hooks config
     * @var array
     */
    protected $config = [];
    /**
     * Current site name
     * @var string
     */
    protected $site = null;

    /**
     * The instance to manager sites(must config multiple sites model)
     * @var SiteManager
     */
    protected $site_manager;

    /**
     * Determine if open single site model
     * @var bool
     */
    protected $isSingle = false;

    /**
     * The constructor.
     * @param Container $app
     * @param SiteManager $site_manager
     */
    public function __construct(Container $app, SiteManager $site_manager)
    {
        $this->app = $app;
        $this->config = $this->app->make('config')->get('hooks');
        $this->site_manager = $site_manager;
        $this->isSingle = isset($this->config['single']) && $this->config['single'];
    }

    /**
     * Get SiteManager instance.
     * @return SiteManager
     */
    public function manager()
    {
        if ($this->isSingle){
            throw new ErrorException("Can not use sites manager in single model!");
        }
        return $this->site_manager;
    }

    /**
     * Set Current Site
     * Config 'hooks.single' must be false or not set
     * @param $site
     * @return $this
     */
    public function site($site)
    {
        if (!$site){
            return $this;
        }
        if ($this->isSingle){
            throw new ErrorException("Can not use specify site in single model!");
        }
        ErrorException::setSite($site);
        if (!$this->site_manager->has($site)) {
            throw new ErrorException("Site {$site} not exists in config!");
        }
        $this->site = $this->site_manager->get($site);
        return $this;
    }

    /**
     * Instantiate the deploy process class & execute deploy command
     */
    public function deploy()
    {
        try{
            $this->siteData();
            $deploy = new Process($this->site,$this->config);
            $deploy->execute();
        }catch (ErrorException $e){
            $this->app['hooks.log']->error($e->errorMessage());
        }
    }
}
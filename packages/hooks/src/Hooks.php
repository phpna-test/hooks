<?php

namespace PHPNa\Hooks;

use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use PHPNa\Hooks\Exceptions\DeployErrorException;
use PHPNa\Hooks\Repository\Deploy;
use PHPNa\Hooks\Repository\SiteManager;

class Hooks
{
    protected $app;
    protected $config;
    protected $token = '';
    protected $site = null;
    protected $deploy;
    protected $branch;
    protected $client_token;
    protected $log;
    protected $site_manager;
    protected $isSingle = false;

    public function __construct(Container $app, SiteManager $site_manager)
    {
        $this->app = $app;
        $this->config = $this->app->make('config')->get('hooks');
        $this->site_manager = $site_manager;
        $this->isSingle = isset($this->config['single']) && $this->config['single'];
    }

    public function siteManager()
    {
        if ($this->isSingle){
            throw new DeployErrorException("Can not use sites manager in single model!");
        }
        return $this->site_manager;
    }

    public function siteConfig($site)
    {
        if (!$site){
            return $this;
        }
        if ($this->isSingle){
            throw new DeployErrorException("Can not use specify site in single model!");
        }
        DeployErrorException::setSite($site);
        if (!$this->site_manager->has($site)) {
            throw new DeployErrorException("Site {$site} not exists in config!");
        }
        $this->site = $this->site_manager->get($site);
        return $this;
    }

    protected function siteData()
    {
        $this->site = $site_data = $this->ConfigData();
        $this->site['type'] = [
            'name' => $site_data['type'],
            'class' => $this->config['types'][$site_data['type']]
        ];
        $this->site['script'] = $this->config['scripts'][$site_data['script']];
        $this->site['script']['name'] = $site_data['script'];
        $this->site['checks'] = [];
        foreach ($site_data['checks'] as $check){
            $this->site['checks'][$check] = $this->config['checks'][$check];
        }
    }
    protected function singleConfig()
    {
        $site = isset($this->config['single_site']) ? $this->config['single_site'] : [];
        $site['path'] = base_path();
        $file = new \SplFileInfo($site['path']);
        $site['name'] = $file->getFilename();
        $site['script'] = 'composer';
        return $site;
    }
    protected function ConfigData()
    {
        $site = $this->site;
        if (!$site) {
            $site = $this->isSingle ? $this->singleConfig() : $this->site_manager->getDefault();
            DeployErrorException::setSite($site['name']);
        }
        $site['secret'] = Arr::get($site,'secret') ?: env('APP_KEY','');
        $site['repository'] = Arr::get($site,'repository') ?: null;
        $site['type'] = Arr::get($site,'type') ?:
            Arr::get($this->config,'defaults.type');
        if (!isset($this->config['types'][$site['type']])) {
            throw new DeployErrorException("Deploy Type [{$site['type']}] not exits!");
        }
//        if (!class_exists($this->config['types'][$this->site['type']])) {
//            throw new TypeNotExistsException("Deploy Type {$this->site['type']} not exits!");
//        }
        $site['script'] = Arr::get($site,'script') ?:
            Arr::get($this->config,'defaults.script');
        if (!isset($this->config['scripts'][$site['script']])) {
            throw new DeployErrorException("Deploy Script [{$site['script']}] not exits!");
        }
        if (!$this->isSingle){
            $site['path'] = "{$this->config['paths']['web']}/{$site['name']}";
            $site['cloned'] = false;
            if (!is_dir($site['path'])){
                if (!isset($site['repository'])){
                    throw new DeployErrorException("Site [{$site['name']}]'s directory not found!");
                }
                $site['cloned'] = true;
            }
        }else{
            $site['cloned'] = true;
        }
        $site['checks'] = isset($site['checks']) ? $site['checks'] : [];
        foreach ($site['checks'] as $check){
            if (!isset($this->config['checks'][$check])){
                throw new DeployErrorException("Client Check named with [{$check}] not exists!");
            }
        }
        $site['client'] = $this->clientData();
        return $site;
    }

    protected function clientData()
    {
        $data = [
            'input' => file_get_contents(Arr::get($this->config, 'paths.root') . '/test.json')
        ];
        $data['data'] = json_decode($data['input'], true);
        $data['branch'] = @end(explode('/', $data['data']["ref"]));
        return $data;
    }

    public function deploy()
    {
        try{
            $this->siteData();
            $deploy = new Deploy($this->site,$this->config);
            $deploy->execute();
        }catch (DeployErrorException $e){
            $this->app['hooks.log']->error($e->errorMessage());
        }
    }
}
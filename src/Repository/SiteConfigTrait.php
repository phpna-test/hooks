<?php
namespace Gkr\Hooks\Repository;

use Illuminate\Support\Arr;
use Gkr\Hooks\Deploy\ErrorException;
trait SiteConfigTrait
{
    /**
     * Generate current site's data
     */
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

    /**
     * Generate Site data in single site model
     * @return array
     */
    protected function singleConfig()
    {
        $site = isset($this->config['single_site']) ? $this->config['single_site'] : [];
        $site['path'] = base_path();
        $file = new \SplFileInfo($site['path']);
        $site['name'] = $file->getFilename();
        $site['script'] = 'composer';
        return $site;
    }

    /**
     * Config current site data for 'siteData' method to use
     * @return array
     */
    protected function ConfigData()
    {
        $site = $this->site;
        if (!$site) {
            $site = $this->isSingle ? $this->singleConfig() : $this->site_manager->getDefault();
            ErrorException::setSite($site['name']);
        }
        $site['secret'] = Arr::get($site,'secret') ?: env('APP_KEY','');
        $site['repository'] = Arr::get($site,'repository') ?: null;
        $site['type'] = Arr::get($site,'type') ?:
            Arr::get($this->config,'defaults.type');
        if (!isset($this->config['types'][$site['type']])) {
            throw new ErrorException("Deploy Type [{$site['type']}] not exits!");
        }
//        if (!class_exists($this->config['types'][$this->site['type']])) {
//            throw new TypeNotExistsException("Deploy Type {$this->site['type']} not exits!");
//        }
        $site['script'] = Arr::get($site,'script') ?:
            Arr::get($this->config,'defaults.script');
        if (!isset($this->config['scripts'][$site['script']])) {
            throw new ErrorException("Deploy Script [{$site['script']}] not exits!");
        }
        if (!$this->isSingle){
            $site['path'] = "{$this->config['paths']['web']}/{$site['name']}";
            $site['clone'] = false;
            if (!is_dir($site['path'])){
                if (!isset($site['repository'])){
                    throw new ErrorException("Site [{$site['name']}]'s directory not found!");
                }
                $site['clone'] = true;
            }
        }else{
            $site['clone'] = false;
        }
        $site['checks'] = isset($site['checks']) ? $site['checks'] : [];
        foreach ($site['checks'] as $check){
            if (!isset($this->config['checks'][$check])){
                throw new ErrorException("Client Check named with [{$check}] not exists!");
            }
        }
        $site['client'] = $this->clientData();
        return $site;
    }

    /**
     * Generate Client Data
     * @return array
     */
    protected function clientData()
    {
        $data = [
            'input' => file_get_contents(Arr::get($this->config, 'paths.root') . '/test.json')
        ];
        $data['data'] = json_decode($data['input'], true);
        $data['branch'] = @end(explode('/', $data['data']["ref"]));
        return $data;
    }
}
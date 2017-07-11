<?php
namespace PHPNa\Hooks\Repository;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Arr;
use PHPNa\Hooks\Exceptions\DeployErrorException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
class SiteManager
{
    protected $config;
    protected $fs;
    protected $config_path;
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->fs = new Filesystem();
        if (!$this->config->get('hooks.single')){
            $this->config_path = storage_path('app/hooks.yml');
            $this->set($this->config->get('hooks.sites'));
        }
    }
    public function reset()
    {
        if ($this->fs->exists($this->config_path)){
            unlink($this->config_path);
        }
        $this->set($this->config->get('hooks.sites'));
    }
    public function set($data = [],$key = null)
    {
        if ($key != null){
            $sites = Yaml::parse(file_get_contents($this->config_path));
            Arr::set($sites,$key,$data);
            $data = $sites;
        }else{
            foreach ($data as $name => $config){
                $data[$name]['name'] = $name;
            }
        }
        file_put_contents($this->config_path, Yaml::dump($data,5));
        $this->config->set('hooks.sites',$data);
    }
    public function get($key = null)
    {
        $data = Yaml::parse(file_get_contents($this->config_path));
        $this->config->set('hooks.sites',$data);
        $key = $key ? ".$key" : '';
        return $this->config->get("hooks.sites$key",[]);
    }
    public function all()
    {
        return $this->get();
    }
    public function getDefault()
    {
        $default_site = $this->config->get('hooks.defaults.site','default');
        if (!$this->has($default_site)){
            throw new DeployErrorException("Default site [{$default_site}] not exits in site manager");
        }
        return $this->get($default_site);
    }
    public function add($name,$site = [])
    {
        if (!is_array($site) || !Arr::accessible($site)){
            throw new DeployErrorException("Site config must be array");
        }
        if (key_exists($name,$this->get())){
            throw new DeployErrorException("Site [{$name}] has be exits in site manager!");
        }
        $this->set($name,$site);
        return $site;
    }
    public function addMulti($sites = []){
        foreach ($sites as $name => $site){
            $this->add($name,$site);
        }
        return $sites;
    }
    public function del($name)
    {
        if (!key_exists($name,$this->get())){
            throw new DeployErrorException("Site [{$name}] not exits in site manager!");
        }
        $sites = $this->get();
        $delete = $sites[$name];
        Arr::pull($sites,$name);
        $this->set($sites);
        return $delete;
    }
    public function delMulti($names = [])
    {
        $sites = [];
        foreach ($names as $name){
            $sites[] = $this->del($name);
        }
        return $sites;
    }
    public function has($name)
    {
        return key_exists($name,$this->get());
    }
}
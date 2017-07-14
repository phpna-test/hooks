<?php
namespace Gkr\Hooks\Repository;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Arr;
use Gkr\Hooks\Contracts\ManagerInterface;
use Gkr\Hooks\Deploy\ErrorException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Site manager for multiple sites model
 * @package Gkr\Hooks\Repository
 */
class SiteManager implements ManagerInterface
{
    protected $config;
    protected $fs;
    protected $config_path;

    /**
     * The constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->fs = new Filesystem();
        $this->config_path = storage_path('app/hooks.yml');
        if (!$this->config->get('hooks.single') && !$this->fs->exists($this->config_path)){
            $this->set($this->config->get('hooks.sites'));
        }
    }

    /**
     * Reset all sites dynamic config
     * It will delete hooks.yml in storage and create a new from config/hooks.php
     */
    public function reset()
    {
        if ($this->fs->exists($this->config_path)){
            unlink($this->config_path);
        }
        $this->set($this->config->get('hooks.sites'));
    }

    /**
     * Set a {key,value} config of sites dynamic config
     * @param array $data
     * @param null $key
     */
    public function set($data = [],$key = null)
    {
        if ($key != null){
            $sites = Yaml::parse(file_get_contents($this->config_path));
            Arr::set($sites,$key,$data);
            $data = $sites;
        }else{
            $data = $this->getInitSites($data);
        }
        file_put_contents($this->config_path, Yaml::dump($data,5));
        $this->config->set('hooks.sites',$data);
    }

    protected function getInitSites($data = [])
    {
        $sites = [];
        foreach ($data as $name => $config){
            $sites[$name] = [];
            $sites[$name]['name'] = $name;
            $sites[$name]['type'] = isset($config['type']) ?
                $config['type'] : $this->config->get('hooks.default.type');
            $sites[$name]['script'] = isset($config['script']) ?
                $config['script'] : $this->config->get('hooks.default.script');
            $sites[$name]['checks'] = isset($config['checks']) ? $config['checks'] : [];
            $sites[$name]['prefix'] = isset($config['prefix']) ? $config['prefix'] : null;
            $sites[$name]['repository'] = isset($config['repository']) ? $config['repository'] : null;
        }
        return $sites;
    }

    /**
     * Get a config of sites dynamic config
     * @param null $key
     * @return mixed
     */
    public function get($key = null)
    {
        $data = Yaml::parse(file_get_contents($this->config_path));
        $this->config->set('hooks.sites',$data);
        $key = $key ? ".$key" : '';
        return $this->config->get("hooks.sites$key",[]);
    }

    /**
     * List all sites & get all sites config
     * @return array
     */
    public function all()
    {
        return $this->get();
    }

    /**
     * Get default site's config which specified in config 'hooks.defaults.site'
     * @return array
     */
    public function getDefault()
    {
        $default_site = $this->config->get('hooks.defaults.site','default');
        if (!$this->has($default_site)){
            throw new ErrorException("Default site [{$default_site}] not exits in site manager");
        }
        return $this->get($default_site);
    }

    /**
     * Add a new site & config it
     * @param $name
     * @param array $site
     * @return array
     */
    public function add($name,$site = [])
    {
        if (!is_array($site) || !Arr::accessible($site)){
            throw new ErrorException("Site config must be array");
        }
        if (key_exists($name,$this->get())){
            throw new ErrorException("Site [{$name}] has be exits in site manager!");
        }
        $this->set($site,$name);
        return $site;
    }

    /**
     * Add multiple sites
     * @param array $sites
     * @return array
     */
    public function addMul($sites = []){
        foreach ($sites as $name => $site){
            $this->add($name,$site);
        }
        return $sites;
    }

    /**
     * Delete a site in dynamic config
     * @param $name
     * @return array
     */
    public function delete($name)
    {
        if (!key_exists($name,$this->get())){
            throw new ErrorException("Site [{$name}] not exits in site manager!");
        }
        $sites = $this->get();
        $delete = $sites[$name];
        Arr::pull($sites,$name);
        $this->set($sites);
        return $delete;
    }

    /**
     * Delete multiple sites
     * @param array $names
     * @return array
     */
    public function delMul($names = [])
    {
        $sites = [];
        foreach ($names as $name){
            $sites[] = $this->delete($name);
        }
        return $sites;
    }

    /**
     * determine if a site exits in dynamic config by its name
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return key_exists($name,$this->get());
    }
}
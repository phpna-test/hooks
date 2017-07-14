<?php
namespace Gkr\Hooks\Contracts;

interface ManagerInterface
{
    /**
     * Reset all sites dynamic config
     * It will delete hooks.yml in storage and create a new from config/hooks.php
     */
    public function reset();

    /**
     * Set a {key,value} config of sites dynamic config
     * @param array $data
     * @param null $key
     */
    public function set($data = [],$key = null);

    /**
     * Get a config of sites dynamic config
     * @param null $key
     * @return mixed
     */
    public function get($key = null);

    /**
     * List all sites & get all sites config
     * @return array
     */
    public function all();

    /**
     * Get default site's config which specified in config 'hooks.defaults.site'
     * @return array
     */
    public function getDefault();

    /**
     * Add a new site & config it
     * @param $name
     * @param array $site
     * @return array
     */
    public function add($name,$site = []);

    /**
     * Add multiple sites
     * @param array $sites
     * @return array
     */
    public function addMul($sites = []);

    /**
     * Delete a site in dynamic config
     * @param $name
     * @return array
     */
    public function delete($name);

    /**
     * Delete multiple sites
     * @param array $names
     * @return array
     */
    public function delMul($names = []);

    /**
     * determine if a site exits in dynamic config by its name
     * @param $name
     * @return bool
     */
    public function has($name);
}
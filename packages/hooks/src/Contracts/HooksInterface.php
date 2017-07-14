<?php
namespace Gkr\Hooks\Contracts;

interface HooksInterface
{
    /**
     * Get SiteManager instance.
     * @return \Gkr\Hooks\Repository\SiteManager
     */
    public function manager();

    /**
     * Set Current Site
     * Config 'hooks.single' must be false or not set
     * @param $site
     * @return $this
     */
    public function site($site);

    /**
     * Instantiate the deploy process class & execute deploy command
     */
    public function deploy();
}
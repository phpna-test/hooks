<?php
namespace PHPNa\Hooks\Listeners;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPNa\Hooks\Hooks;

class DeployListener implements ShouldQueue
{
    public $connection = 'hooks';
    public $queue = 'hooks';
    public $tries = 3;
    public $timeout = 120;
    protected $hooks;
    public function __construct(Hooks $hooks)
    {
        $this->hooks = $hooks;
    }

    public function handle($site = null)
    {
        $this->hooks->siteConfig($site)->deploy();
    }
}
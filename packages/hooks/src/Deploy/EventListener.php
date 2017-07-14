<?php
namespace Gkr\Hooks\Deploy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Config\Repository as Config;
use Gkr\Hooks\Contracts\HooksInterface;

class EventListener implements ShouldQueue
{
    protected $config;
    public $connection = 'hooks';
    public $tries = 3;
    public $timeout = 120;
    protected $hooks;
    public function __construct(Config $config,HooksInterface $hooks)
    {
        $this->hooks = $hooks;
        $this->timeout = $config->get('hooks.queue.timeout') ?: $this->timeout;
        $this->tries = $config->get('hooks.queue.tries') ?: $this->tries;
    }

    public function handle($site = null)
    {
        $this->hooks->site($site)->deploy();
    }
}
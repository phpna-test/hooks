<?php
namespace Gkr\Hooks\Commands;
use Gkr\Hooks\Contracts\HooksInterface;
use Illuminate\Console\Command;

/**
 * Show all sites and their config command
 * @package Gkr\Hooks\Commands
 */
class ShowCommand extends Command
{
    protected $signature = 'hooks:show';
    protected $description = 'Show all sites and its config';
    protected $hooks;
    public function __construct(HooksInterface $hooks)
    {
        parent::__construct();
        $this->hooks = $hooks;
    }

    public function handle()
    {
        $headers = ['name','type','script','checks','prefix','repository'];
        $sites = [];
        foreach ($this->hooks->manager()->all() as $name => $site){
            $sites[$name] = $site;
            $sites[$name]['checks'] = !empty($site['checks']) ? '['.implode(',',$site['checks']).']' : '[]';
        }
        $this->table($headers,$sites);
    }
}
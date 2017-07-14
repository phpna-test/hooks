<?php
namespace Gkr\Hooks\Commands;

use Gkr\Hooks\Contracts\HooksInterface;
use Gkr\Hooks\Contracts\LoggerInterface;
use Gkr\Hooks\Deploy\ErrorException;
use Illuminate\Console\Command;

/**
 * Reset sites config command
 * @package Gkr\Hooks\Commands
 */
class ResetCommand extends Command
{
    protected $signature = 'hooks:reset';
    protected $description = 'Reset all sites config with init config';
    protected $hooks;
    protected $logger;
    public function __construct(HooksInterface $hooks,LoggerInterface $logger)
    {
        parent::__construct();
        $this->hooks = $hooks;
        $this->logger = $logger;
    }

    public function handle()
    {
        $message = "Reset sites config will delete all your custom sites and init with 'hooks.sites' config,do you want to continue?";
        if ($this->confirm($message,false)) {
            try{
                $this->hooks->manager()->reset();
                $this->info("Reset sites successful!");
            }catch (ErrorException $e){
                $this->logger->error($e->errorMessage());
                $this->error($e->errorMessage());
            }
        }
    }
}
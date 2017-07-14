<?php
namespace Gkr\Hooks\Commands;
use Gkr\Hooks\Contracts\HooksInterface;
use Gkr\Hooks\Contracts\LoggerInterface;
use Gkr\Hooks\Deploy\ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

/**
 * Delete site command
 * @package Gkr\Hooks\Commands
 */
class DeleteCommand extends Command
{
    protected $signature = 'hooks:delete';
    protected $description = 'Delete sites in config of deploy';
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
        $sites = array_keys($this->hooks->manager()->all());
        $sites = Arr::prepend($sites,'none');
        $deletes = $this->choice(
            'Select which sites you want to delete (multiple selector)',
            $sites,
            0,
            null,
            true
        );
        if (in_array('none',$deletes)){
            $deletes = [];
        }
        try{
            if (empty($deletes)){
                $this->info('You not seleted any sites to delete');
                exit();
            }
            $this->hooks->manager()->delMul($deletes);
            $delete_names = "[".implode(',',$deletes)."]";
            $this->info("Delete sites $delete_names successful!");
        }catch (ErrorException $e){
            $this->logger->error($e->errorMessage());
            $this->error($e->errorMessage());
        }
    }
}
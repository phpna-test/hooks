<?php
namespace Gkr\Hooks\Commands;
use Gkr\Hooks\Contracts\HooksInterface;
use Gkr\Hooks\Contracts\LoggerInterface;
use Gkr\Hooks\Deploy\ErrorException;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

/**
 * Add site command
 * @package Gkr\Hooks\Commands
 */
class MakeCommand extends Command
{
    protected $signature = 'hooks:make {site}';
    protected $description = 'Add a site to hooks';
    protected $hooks;
    protected $logger;
    protected $config;
    public function __construct(HooksInterface $hooks,LoggerInterface $logger)
    {
        parent::__construct();
        $this->config = app('config');
        $this->hooks = $hooks;
        $this->logger = $logger;
    }
    public function handle()
    {
        ErrorException::setSite($this->argument('site'));
        try{
            $this->hooks->manager()->add($this->argument('site'),$this->getConfig());
            $this->info("Add site named {$this->argument('site')} successful!");
        }catch (ErrorException $e){
            $this->logger->error($e->errorMessage());
            $this->error($e->errorMessage());
        }
    }
    protected function getConfig()
    {
        $config = [];
        $defaults = $this->config->get('hooks.defaults');
        $types = array_keys($this->config->get('hooks.types'));
        $scripts = array_keys($this->config->get('hooks.scripts'));
        $checks = array_keys($this->config->get('hooks.checks'));
        $checks = Arr::prepend($checks,'none');
        $messages = [
            "Which type of git platform the site use?",
            "Which script for site deploy?",
            "Which checks for site before deploy (multiple selector)?",
            "What prefix of deploy shell?",
            "Repository of site to clone?"
        ];
        $config['name'] = $this->argument('site');
        $config['type'] = $this->choice($messages[0],$types,array_search($defaults['type'],$types));
        $config['script'] = $this->choice($messages[1],$scripts,array_search($defaults['script'],$scripts));
        $checks_value = $this->choice($messages[2],$checks,0,null,true);
        $config['checks'] = in_array('none',$checks_value) ? [] : $checks_value;
        $config['prefix'] = $this->output->ask($messages[3],null,function($value){
            if (!is_string($value) && $value != null){
                $this->error('Value of command prefix must be string type or null!');
            }
            return $value;
        });
        $config['repository'] = $this->output->ask($messages[4],null,function($value){
            if (!is_string($value) && $value != null){
                $this->error('Value of repository must be string type or null!');
            }
            return $value;
        });
        return $config;
    }
}
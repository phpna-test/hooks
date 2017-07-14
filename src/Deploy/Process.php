<?php
namespace Gkr\Hooks\Deploy;

use Illuminate\Support\Arr;
use Symfony\Component\Process\Process as Shell;

/**
 * Hooks deploy class
 * @package Gkr\Hooks\Deploy
 */
class Process
{
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var array
     */
    protected $site_data = [];

    /**
     * The constructor.
     * @param $site_data
     * @param $config
     */
    public function __construct($site_data,$config)
    {
        $this->config = $config;
        $this->site_data = $site_data;
    }

    /**
     * Execute deploy command
     */
    public function execute()
    {
        $deploy_class = $this->site_data['type']['class'];
        $deploy = new $deploy_class($this->site_data);
        if ($this->check($deploy)){
            $process = new Shell($this->getCommand());
            $process->start();
            $process->wait(function ($type, $buffer) {
                app('hooks.log')->info("Site [{$this->site_data['name']}] Deploy:".PHP_EOL.$buffer);
            });
            $process->stop();
        }
    }

    /**
     * Check client info like token or ip...
     * @param $deploy
     * @return bool
     */
    protected function check($deploy)
    {
        $result = true;
        foreach ($this->site_data['checks'] as $check){
            $check_container = new $check;
            if (!$check_container->check($deploy)){
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Generate the deploy command
     * @return string
     */
    protected function getCommand()
    {
        $file = $this->site_data['script']['file'];
        $shell = $this->site_data['script']['shell'];
        $shellMethod = "exec".strtoupper($shell);
        if (!method_exists(get_class($this), $shellMethod)) {
            throw new ErrorException("Script shell of [{$shell}] has not implement");
        }
        $commands[] = isset($this->site_data['prefix']) ? [$this->site_data['prefix']] : [];
        $commands[] = $this->site_data['clone'] ? [
            "cd {$this->config['paths']['web']}",
            "git clone {$this->site_data['repository']} {$this->site_data['name']}",
        ] : [
            "cd {$this->site_data['path']}",
            "{$this->$shellMethod($file)}",
        ];
        $commands = Arr::collapse($commands);
        return implode(" && ",$commands);
    }

    /**
     * Execute php type shell
     * @param $file
     * @return string
     */
    protected function execPHP($file)
    {
        $php_cmd = $this->config['bins']['php'] ?: 'php';
        return "{$php_cmd} $file {$this->site_data['client']['branch']}";
    }
}
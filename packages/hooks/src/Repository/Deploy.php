<?php
namespace PHPNa\Hooks\Repository;

use Illuminate\Support\Arr;
use PHPNa\Hooks\Exceptions\DeployErrorException;
use Symfony\Component\Process\Process;

class Deploy
{
    protected $config = [];
    protected $site_data;
    protected $deploy_type;
    public function __construct($site_data,$config)
    {
        $this->config = $config;
        $this->site_data = $site_data;
    }
    public function execute()
    {
        $deploy_class = $this->site_data['type']['class'];
        $deploy = new $deploy_class($this->site_data);
        if ($this->check($deploy)){
            $process = new Process($this->getCommand());
            $process->start();
            $process->wait(function ($type, $buffer) {
                app('hooks.log')->info("Site [{$this->site_data['name']}] Deploy:".PHP_EOL.$buffer);
            });
            $process->stop();
        }
    }
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
    protected function getCommand()
    {
        $file = $this->site_data['script']['file'];
        $shell = $this->site_data['script']['shell'];
        $shellMethod = "exec".strtoupper($shell);
        if (!method_exists(get_class($this), $shellMethod)) {
            throw new DeployErrorException("Script shell of [{$shell}] has not implement");
        }
        $commands[] = isset($this->site_data['prefix']) ? [$this->site_data['prefix']] : [];
        $commands[] = $this->site_data['cloned'] ? [
            "cd {$this->config['paths']['web']}",
            "git clone {$this->site_data['repository']} {$this->site_data['name']}",
        ] : [
            "cd {$this->site_data['path']}",
            "{$this->$shellMethod($file)}",
        ];
        $commands = Arr::collapse($commands);
        return implode(" && ",$commands);
    }

    protected function execPHP($file)
    {
        $php_cmd = $this->config['bins']['php'] ?: 'php';
        return "{$php_cmd} $file {$this->site_data['client']['branch']}";
    }
}
<?php
namespace PHPNa\Hooks\Types;
abstract class BaseType
{
    protected $config;
    public function __construct($config)
    {
        $this->config = $config;
    }
}
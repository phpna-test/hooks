<?php
namespace Gkr\Hooks\Code\Types;
abstract class BaseType
{
    protected $config;
    public function __construct($config)
    {
        $this->config = $config;
    }
}
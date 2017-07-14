<?php
namespace Gkr\Hooks\Code\Types;
use Gkr\Hooks\Contracts\LoggerInterface;

abstract class BaseType
{
    protected $config;
    protected $logger;
    public function __construct($config,LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }
}
<?php
namespace PHPNa\Hooks\Contracts;
interface TokenInterface
{
    public function generateServerToken();
    public function generateClientToken();
}
<?php
namespace PHPNa\Hooks\Checks;
use PHPNa\Hooks\Contracts\TokenInterface;
use PHPNa\Hooks\Exceptions\DeployErrorException;

class TokenCheck
{
    public function check(TokenInterface $deploy)
    {
        if ($deploy->generateServerToken() == $deploy->generateServerToken()){
            return true;
        }
        throw new DeployErrorException("Server token not equal client token!");
    }
}
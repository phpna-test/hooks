<?php
namespace Gkr\Hooks\Code\Checks;
use Gkr\Hooks\Deploy\ErrorException;

class TokenCheck
{
    public function check(TokenInterface $deploy)
    {
        if ($deploy->generateServerToken() == $deploy->generateServerToken()){
            return true;
        }
        throw new ErrorException("Server token not equal client token!");
    }
}
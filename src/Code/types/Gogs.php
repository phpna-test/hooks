<?php
namespace Gkr\Hooks\Code\Types;

use Gkr\Hooks\Code\Checks\TokenInterface;

class Gogs extends BaseType implements TokenInterface
{
    public function generateServerToken()
    {
        $token = hash_hmac('sha256', file_get_contents("php://input"), $this->config['secret'], false);
        return $token;
    }

    public function generateClientToken()
    {
        return $_SERVER['HTTP_X_GOGS_SIGNATURE'];
    }
}

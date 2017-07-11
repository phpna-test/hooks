<?php
namespace PHPNa\Hooks\Types;

use PHPNa\Hooks\Contracts\TokenInterface;

class Gogs extends BaseType implements TokenInterface
{
    public function generateServerToken()
    {
        return hash_hmac('sha256', $this->config['client']['input'], $this->config['secret'], false);
    }

    public function generateClientToken()
    {
        $_SERVER['HTTP_X_GOGS_SIGNATURE'] = 'cce0094888f535739030a56202ccf4b91c44898cd4eb4ee4c81d83c6463f780e';
        return $_SERVER['HTTP_X_GOGS_SIGNATURE'];
    }
}

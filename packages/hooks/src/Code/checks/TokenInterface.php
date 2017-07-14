<?php
namespace Gkr\Hooks\Code\Checks;
interface TokenInterface
{
    public function generateServerToken();
    public function generateClientToken();
}
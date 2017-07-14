<?php
namespace Gkr\Hooks\Deploy;
class ErrorException extends \RuntimeException
{
    private static $site = null;
    public static function setSite($site)
    {
        self::$site = $site;
    }
    public function errorMessage()
    {
        $site = self::$site;
        $msg = $site ? "Site [$site] " : "";
        return "{$msg}Config: ".PHP_EOL.$this->getMessage();
    }
}
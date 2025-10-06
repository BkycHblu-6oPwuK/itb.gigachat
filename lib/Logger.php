<?php

namespace Beeralex\Gigachat;

use Beeralex\Core\Logger\FileLogger;

class Logger extends FileLogger
{
    public function __construct()
    {
        $filename = date('Y-m-d') . '.log';
        parent::__construct(__DIR__ . "/../logs/{$filename}");
    }
}

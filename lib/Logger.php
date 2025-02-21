<?php

namespace Itb\Gigachat;

use Itb\Gigachat\Contracts\Logger as ContractsLogger;

class Logger implements ContractsLogger
{
    public function log(string|array $data): void
    {
        if (Options::getInstance()->logErrors) {
            $filename = date('Y-m-d') . '.log';
            if (is_array($data)) {
                $log = date('H:i:s') . ' ' . print_r($data, true);
            } else {
                $log = date('H:i:s') . ' ' . $data;
            }
            file_put_contents(__DIR__ . "/../logs/{$filename}", $log . PHP_EOL, FILE_APPEND);
        }
    }
}

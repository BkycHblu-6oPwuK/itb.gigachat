<?php
namespace Itb\Gigachat;

class CacheSettings
{
    public readonly int $time;
    public readonly string $key;
    public readonly string $dir;

    public function __construct(int $time = 0, string $key = '', string $dir = '')
    {
        $this->time = $time;
        $this->key = $key;
        $this->dir = $dir;
    }
}
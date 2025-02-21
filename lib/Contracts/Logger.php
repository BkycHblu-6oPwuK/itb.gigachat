<?php
namespace Itb\Gigachat\Contracts;

interface Logger
{
    public function log(string|array $data): void;
}
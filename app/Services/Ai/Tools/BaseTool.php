<?php

namespace App\Services\Ai\Tools;

abstract class BaseTool
{
    /**
     * Definitions of all functions/tools in this section in JSON Schema format
     */
    abstract public function definitions(): array;

    /**
     * Execute a tool by function name and parameters
     */
    abstract public function execute(string $name, array $arguments): mixed;
}

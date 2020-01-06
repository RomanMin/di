<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

class ReflectionClass
{
    private $dependencies;
    private $instantiable;

    public function __construct(bool $instantiable = false, string ...$dependencies)
    {
        $this->instantiable = $instantiable;
        $this->dependencies = $dependencies;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function isInstantiable(): bool
    {
        return $this->instantiable;
    }
}

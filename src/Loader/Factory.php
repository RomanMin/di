<?php

declare(strict_types=1);

namespace Cekta\DI\Loader;

use Cekta\DI\Loader;
use Psr\Container\ContainerInterface;

class Factory implements Loader
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string[]
     */
    private $dependencies;

    public function __construct(string $className, string ...$dependencies)
    {
        $this->className = $className;
        $this->dependencies = $dependencies;
    }

    public function __invoke(ContainerInterface $container)
    {
        $args = [];
        foreach ($this->dependencies as $dependecy) {
            $args[] = $container->get($dependecy);
        }
        return new $this->className(...$args);
    }
}

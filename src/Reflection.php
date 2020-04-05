<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Reflection
{
    private $instantiable = [];
    private $dependencies = [];
    private $variadic = [];
    /**
     * @var ReflectionTransformer[]
     */
    private $transformers;

    public function __construct(ReflectionTransformer ...$tranfromers)
    {
        $this->transformers = $tranfromers;
    }

    /**
     * @param string $name
     * @return string[]
     * @internal
     */
    public function getDependencies(string $name): array
    {
        if (!array_key_exists($name, $this->dependencies)) {
            $this->load($name);
        }
        return $this->dependencies[$name];
    }

    /**
     * @param string $name
     * @return bool
     * @internal
     */
    public function isVariadic(string $name): bool
    {
        if (!array_key_exists($name, $this->variadic)) {
            $this->load($name);
        }
        return $this->variadic[$name];
    }

    /**
     * @param string $name
     * @return bool
     * @internal
     */
    public function isInstantiable(string $name): bool
    {
        if (!array_key_exists($name, $this->instantiable)) {
            $this->load($name);
        }
        return $this->instantiable[$name];
    }

    private function load(string $name): void
    {
        try {
            $class = new ReflectionClass($name);
            $this->instantiable[$name] = $class->isInstantiable();
            $dependencies = self::getMethodDependencies($class->getConstructor());
            $this->variadic[$name] = $dependencies[0];
            $this->dependencies[$name] = $this->tranform($class->getName(), $dependencies[1]);
        } catch (ReflectionException $exception) {
            $this->dependencies[$name] = [];
            $this->instantiable[$name] = false;
            $this->variadic[$name] = false;
        }
    }

    private static function getMethodDependencies(?ReflectionMethod $method): array
    {
        $variadic = false;
        $parameters = [];
        if ($method !== null) {
            foreach ($method->getParameters() as $parameter) {
                $variadic = $parameter->isVariadic();
                $class = $parameter->getClass();
                $parameters[] = $class && $variadic !== true ? $class->name : $parameter->name;
            }
        }
        return [$variadic, $parameters];
    }

    private function tranform(string $name, array $params)
    {
        foreach ($this->transformers as $tranfromer) {
            $params = $tranfromer->transform($name, $params);
        }
        return $params;
    }
}

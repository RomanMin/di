<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Reflection
{
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
        try {
            $class = new ReflectionClass($name);
            $dependencies = self::getMethodDependencies($class->getConstructor());
            return $this->tranform($class->getName(), $dependencies);
        } catch (ReflectionException $exception) {
            return [];
        }
    }

    /**
     * @param string $name
     * @return bool
     * @internal
     */
    public function isVariadic(string $name): bool
    {
        try {
            $class = new ReflectionClass($name);
            if ($class->getConstructor() !== null) {
                $parameters = $class->getConstructor()->getParameters();
                $count = count($parameters);
                return $count > 0 ? $parameters[$count - 1]->isVariadic() : false;
            }
            return false;
        } catch (ReflectionException $exception) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return bool
     * @internal
     */
    public function isInstantiable(string $name): bool
    {
        try {
            $class = new ReflectionClass($name);
            return $class->isInstantiable();
        } catch (ReflectionException $exception) {
            return false;
        }
    }

    private static function getMethodDependencies(?ReflectionMethod $method): array
    {
        $parameters = [];
        if ($method !== null) {
            $annotations = self::getAnnotationParameters((string) $method->getDocComment());
            foreach ($method->getParameters() as $parameter) {
                $class = $parameter->getClass();
                $name = $class && $parameter->isVariadic() !== true ? $class->name : $parameter->name;
                $parameters[$name] = array_key_exists($name, $annotations) ? $annotations[$name] : $name;
            }
        }
        return array_values($parameters);
    }

    private function tranform(string $name, array $params)
    {
        foreach ($this->transformers as $tranfromer) {
            $params = $tranfromer->transform($name, $params);
        }
        return $params;
    }

    private static function getAnnotationParameters(string $comment)
    {
        $result = [];
        $matches = [];
        preg_match_all("/@inject \\\\?([\w\d\\\\]*) \\$([\w\d]*)/", $comment, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $result[$match[2]] = $match[1];
        }
        return $result;
    }
}

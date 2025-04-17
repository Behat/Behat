<?php

declare(strict_types=1);

namespace Behat\Config\Converter;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;

class ConfigConverterTools
{
    private static ?BuilderFactory $builderFactory = null;

    public static function addMethodCall(string $class, string $methodName, array $arguments, Expr $expr): Expr
    {
        $builderFactory = self::getBuilderFactory();
        $arguments = self::removeDefaultArguments((new ReflectionClass($class))->getMethod($methodName), $arguments);
        $args = $builderFactory->args(self::replaceClassReferences($arguments));
        return $builderFactory->methodCall($expr, $methodName, $args);
    }

    private static function removeDefaultArguments(ReflectionMethod $method, array $arguments): array
    {
        $hasRemovedAny = false;
        $result = [];
        foreach ($method->getParameters() as $index => $parameter) {
            if ($parameter->isVariadic()) {
                // We can't do anything useful (easily) for methods with variadics, just return the originals
                return $arguments;
            }

            // Config objects can export their method arguments as positional or named arguments.
            $argKey = array_key_exists($parameter->getName(), $arguments) ? $parameter->getName() : $index;

            // The Config object must provide a value for every argument (defaults are stripped here). If we are
            // missing arguments, that implies the ->toPhpExpr implementation is out of sync with the method definition.
            assert(
                array_key_exists($argKey, $arguments),
                sprintf(
                    'Missing argument #%d (%s) for %s::%s',
                    $index,
                    $parameter->getName(),
                    $method->getDeclaringClass()->getName(),
                    $method->getName(),
                ),
            );

            $argValue = $arguments[$argKey];
            if ($parameter->isDefaultValueAvailable() && $parameter->getDefaultValue() === $argValue) {
                // We can skip this, it has the default value. That means we'll need to specify any following arguments
                // by name even if we were given them as positional.
                $hasRemovedAny = true;
            } else {
                $result[($hasRemovedAny ? $parameter->getName() : $argKey)] = $argValue;
            }

            // Record that this argument has been used
            unset($arguments[$argKey]);
        }

        // We should have applied all arguments that were provided. If not, that implies the ->toPhpExpr implementation
        // is out of sync with the method definition.
        assert(
            $arguments === [],
            sprintf(
                'Too many arguments provided for %s::%s (%s)',
                $method->getDeclaringClass()->getName(),
                $method->getName(),
                implode(', ', array_keys($arguments)),
            ),
        );

        return $result;
    }

    public static function createObject(string $className): New_
    {
        $builderFactory = self::getBuilderFactory();
        return $builderFactory->new(new FullyQualified($className));
    }

    public static function addArgumentsToConstructor(array $arguments, New_ $expr)
    {
        $builderFactory = self::getBuilderFactory();
        $expr->args = $builderFactory->args(self::replaceClassReferences($arguments));
    }

    public static function createUseStatement(string $className): Use_
    {
        $builderFactory = self::getBuilderFactory();
        return $builderFactory->use($className)->getNode();
    }

    private static function getBuilderFactory(): BuilderFactory
    {
        if (!self::$builderFactory instanceof BuilderFactory) {
            self::$builderFactory = new BuilderFactory();
        }
        return self::$builderFactory;
    }

    private static function replaceClassReferences(array $arguments): array
    {
        return array_map(
            static fn ($arg) => is_string($arg) && class_exists($arg)
                ? self::getClassConstReference($arg, 'class')
                : $arg,
            $arguments,
        );
    }

    private static function getClassConstReference(string $className, string $constantName): Expr
    {
        $builderFactory = self::getBuilderFactory();
        return $builderFactory->classConstFetch($className, $constantName);
    }

    public static function findReferenceToClassConstant(string $className, mixed $settingValue): mixed
    {
        $consts = (new ReflectionClass($className))->getConstants(ReflectionClassConstant::IS_PUBLIC);
        foreach ($consts as $constantName => $constantValue) {
            if ($settingValue === $constantValue) {
                return ConfigConverterTools::getClassConstReference($className, $constantName);
            }
        }
        // Can't find a constant for it, just return the original value
        return $settingValue;
    }
}

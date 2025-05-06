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
                $result[$hasRemovedAny ? $parameter->getName() : $argKey] = $argValue;
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

    public static function errorReportingToConstants(mixed $value): Expr
    {
        $builderFactory = self::getBuilderFactory();

        if (!is_int($value)) {
            return $builderFactory->val($value);
        }

        // First remove the old E_STRICT value from both E_ALL and from the actual configured value.
        // This will make the behaviour consistent across all supported PHP versions. Since 8.4.0,
        // use of E_STRICT is deprecated and the value is no longer included in E_ALL. However,
        // the bit may still be set in the error_reporting integer in a user's YAML file.
        // Since 8.0, PHP cannot actually ever trigger an E_STRICT error, so the setting of the bit
        // is actually irrelevant and we can safely ignore it.
        $value = $value & ~2048;

        if ($value === (E_ALL & ~2048)) {
            // The value is equivalent to E_ALL (with or without the E_STRICT bit set, it doesn't matter).
            return $builderFactory->constFetch('E_ALL');
        }

        if ($value === 0) {
            // error_reporting is disabled
            return $builderFactory->val(0);
        }

        // Split the value into constant expressions for the bit flags that are on and off.
        $flagsOn = $flagsOff = [];
        $remainingValue = $value;
        foreach (self::listErrorLevelConstants() as $constName => $constValue) {
            $remainingValue = $remainingValue & ~$constValue;

            if ($value & $constValue) {
                $flagsOn[] = $builderFactory->constFetch($constName);
            } else {
                $flagsOff[] = $builderFactory->constFetch($constName);
            }
        }

        if ($remainingValue !== 0) {
            // This is an edge case, their value includes a number that is not defined as a PHP error_reporting level
            // Keep it as a constant value so that the configuration doesn't change and the user can review it
            $flagsOn[] = $builderFactory->val($remainingValue);
        }

        if (count($flagsOff) < count($flagsOn)) {
            // Most flags are on, it will be more readable and future-proof to start from E_ALL and negate
            return new Expr\BinaryOp\BitwiseAnd(
                $builderFactory->constFetch('E_ALL'),
                new Expr\BitwiseNot(self::buildBitwiseFlagExpression(...$flagsOff)),
            );
        }

        // Most flags are off, so just specify the ones that are on
        return self::buildBitwiseFlagExpression(...$flagsOn);
    }

    private static function listErrorLevelConstants(): iterable
    {
        foreach (get_defined_constants(true)['Core'] as $constName => $constValue) {
            if (str_starts_with($constName, 'E_') && $constName !== 'E_ALL' && $constName !== 'E_STRICT') {
                yield $constName => $constValue;
            }
        }
    }

    private static function buildBitwiseFlagExpression(Expr ...$expressions): Expr
    {
        $expr = array_shift($expressions);
        while ($expressions !== []) {
            $expr = new Expr\BinaryOp\BitwiseOr($expr, array_shift($expressions));
        }

        return $expr;
    }
}

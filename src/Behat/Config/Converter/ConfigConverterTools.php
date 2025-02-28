<?php

declare(strict_types=1);

namespace Behat\Config\Converter;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Use_;

class ConfigConverterTools
{
    private static ?BuilderFactory $builderFactory = null;

    public static function addMethodCall(string $methodName, array $arguments, Expr $expr): Expr
    {
        $builderFactory = self::getBuilderFactory();
        $args = $builderFactory->args($arguments);
        return $builderFactory->methodCall($expr, $methodName, $args);
    }

    public static function createObject(string $className): New_
    {
        $builderFactory = self::getBuilderFactory();
        return $builderFactory->new(new FullyQualified($className));
    }

    public static function addArgumentsToConstructor(array $arguments, New_ $expr)
    {
        $builderFactory = self::getBuilderFactory();
        $expr->args = $builderFactory->args($arguments);
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
}

<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

use Behat\Config\ConfigConverterInterface;
use Behat\Config\Converter\ConfigConverterTools;
use PhpParser\Node\Expr;

class Filter implements FilterInterface, ConfigConverterInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $value,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @internal
     */
    public function toPhpExpr(): Expr
    {
        $filterObject = ConfigConverterTools::createObject(static::class);

        ConfigConverterTools::addArgumentsToConstructor([$this->value], $filterObject);

        return $filterObject;
    }
}

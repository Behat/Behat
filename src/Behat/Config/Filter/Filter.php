<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

use Behat\Config\ConfigConverterInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

class Filter implements FilterInterface, ConfigConverterInterface
{
    private BuilderFactory $builderFactory;

    public function __construct(
        private readonly string $name,
        private readonly string $value,
    ) {
        $this->builderFactory = new BuilderFactory();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function toPhpExpr(): Expr
    {
        $filterObject =  $this->builderFactory->new(new FullyQualified(static::class));

        $args = $this->builderFactory->args([$this->value]);
        $filterObject->args = $args;

        return $filterObject;
    }
}

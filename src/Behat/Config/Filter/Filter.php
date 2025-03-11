<?php

declare(strict_types=1);

namespace Behat\Config\Filter;

use Behat\Config\ConfigConverterInterface;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

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
    public function toPhpExpr(BuilderFactory $builderFactory): Expr
    {
        $filterObject =  $builderFactory->new(new FullyQualified(static::class));

        $filterObject->args = $builderFactory->args([$this->value]);

        return $filterObject;
    }
}

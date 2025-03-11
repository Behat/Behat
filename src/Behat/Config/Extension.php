<?php

declare(strict_types=1);

namespace Behat\Config;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

final class Extension implements ExtensionConfigInterface
{
    public function __construct(
        private string $name,
        private array $settings = [],
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    public function toPhpExpr(BuilderFactory $builderFactory): Expr
    {
        $extensionObject =  $builderFactory->new(new FullyQualified(self::class));

        if ($this->settings === []) {
            $args = $builderFactory->args([$this->name]);
        } else {
            $args = $builderFactory->args([$this->name, $this->settings]);
        }
        $extensionObject->args = $args;

        return $extensionObject;
    }
}

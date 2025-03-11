<?php

declare(strict_types=1);

namespace Behat\Config;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\Node\Name\FullyQualified;

final class Extension implements ExtensionConfigInterface
{
    private BuilderFactory $builderFactory;

    public function __construct(
        private string $name,
        private array $settings = [],
    ) {
        $this->builderFactory = new BuilderFactory();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    public function toPhpExpr(): Expr
    {
        $extensionObject =  $this->builderFactory->new(new FullyQualified(self::class));

        if ($this->settings === []) {
            $args = $this->builderFactory->args([$this->name]);
        } else {
            $args = $this->builderFactory->args([$this->name, $this->settings]);
        }
        $extensionObject->args = $args;

        return $extensionObject;
    }
}

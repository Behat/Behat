<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
use PhpParser\Node\Expr;

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

    public function toPhpExpr(): Expr
    {
        $extensionObject =  ConfigConverterTools::createObject(self::class);

        if ($this->settings === []) {
            $arguments = [$this->name];
        } else {
            $arguments = [$this->name, $this->settings];
        }
        ConfigConverterTools::addArgumentsToConstructor($arguments, $extensionObject);

        return $extensionObject;
    }
}

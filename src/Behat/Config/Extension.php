<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
use Behat\Testwork\ServiceContainer\ExtensionManager;
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
        $extensionObject = ConfigConverterTools::createObject(self::class);

        $name = $this->name;
        if (!class_exists($name)) {
            // It might be a shorthand reference to the extension - attempt to convert to an FQCN
            $fullName = ExtensionManager::guessFullExtensionClassName($name);
            if (class_exists($fullName)) {
                $name = $fullName;
            }
        }

        if ($this->settings === []) {
            $arguments = [$name];
        } else {
            $arguments = [$name, $this->settings];
        }
        ConfigConverterTools::addArgumentsToConstructor($arguments, $extensionObject);

        return $extensionObject;
    }
}

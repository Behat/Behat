<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;

use function is_string;

final class Config implements ConfigInterface, ConfigConverterInterface
{
    public const IMPORTS_SETTING = 'imports';
    private const PREFERRED_PROFILE_NAME_SETTING = 'preferredProfileName';

    private const IMPORT_FUNCTION = 'import';
    private const PROFILE_FUNCTION = 'withProfile';
    private const PREFERRED_PROFILE_FUNCTION = 'withPreferredProfile';

    private BuilderFactory $builderFactory;

    public function __construct(
        private array $settings = []
    ) {
        $this->builderFactory = new BuilderFactory();
    }

    /** @param string|string[] $resource **/
    public function import(string|array $resource): self
    {
        $resources = is_string($resource) ? [$resource] : $resource;

        foreach ($resources as $resource) {
            $this->settings[self::IMPORTS_SETTING][] = $resource;
        }

        return $this;
    }

    public function withProfile(Profile $profile): self
    {
        if (array_key_exists($profile->name(), $this->settings)) {
            throw new ConfigurationLoadingException(sprintf('The profile "%s" already exists.', $profile->name()));
        }

        $this->settings[$profile->name()] = $profile->toArray();

        return $this;
    }

    public function withPreferredProfile(string $profileName): self
    {
        $this->settings[self::PREFERRED_PROFILE_NAME_SETTING] = $profileName;

        return $this;
    }

    public function toArray(): array
    {
        return $this->settings;
    }

    public function toPhpExpr(): Node\Expr
    {
        $configObject =  $this->builderFactory->new(new FullyQualified(self::class));
        $expr = $configObject;

        foreach ($this->settings as $settingsName => $settings) {
            if ($settingsName === self::PREFERRED_PROFILE_NAME_SETTING) {
                $args = $this->builderFactory->args([$settings]);
                $expr = $this->builderFactory->methodCall($expr, self::PREFERRED_PROFILE_FUNCTION, $args);
            } elseif ($settingsName === self::IMPORTS_SETTING) {
                if (count($settings) === 1) {
                    $args = $this->builderFactory->args([$settings[0]]);
                } else {
                    $args = $this->builderFactory->args([$settings]);
                }
                $expr = $this->builderFactory->methodCall($expr, self::IMPORT_FUNCTION, $args);
            } else {
                $profile = new Profile($settingsName, $settings ?? []);
                $args = $this->builderFactory->args([$profile->toPhpExpr()]);
                $expr = $this->builderFactory->methodCall($expr, self::PROFILE_FUNCTION, $args);
            }
            unset($this->settings[$settingsName]);
        }

        if (count($this->settings) !== 0) {
            $args = $this->builderFactory->args([$this->settings]);
            $configObject->args = $args;
        }

        return $expr;
    }
}

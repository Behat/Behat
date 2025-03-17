<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Config\Converter\ConfigConverterTools;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use PhpParser\Node;
use PhpParser\Node\Expr;

use function is_string;

final class Config implements ConfigInterface, ConfigConverterInterface
{
    public const IMPORTS_SETTING = 'imports';
    private const PREFERRED_PROFILE_NAME_SETTING = 'preferredProfileName';

    private const IMPORT_FUNCTION = 'import';
    private const PROFILE_FUNCTION = 'withProfile';
    private const PREFERRED_PROFILE_FUNCTION = 'withPreferredProfile';

    public function __construct(
        private array $settings = [],
    ) {
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

    /**
     * @internal
     */
    public function toPhpExpr(): Expr
    {
        $configObject =  ConfigConverterTools::createObject(self::class);
        $expr = $configObject;

        foreach ($this->settings as $settingsName => $settings) {
            if ($settingsName === self::PREFERRED_PROFILE_NAME_SETTING) {
                $expr = ConfigConverterTools::addMethodCall(
                    self::PREFERRED_PROFILE_FUNCTION,
                    [$settings],
                    $expr
                );
            } elseif ($settingsName === self::IMPORTS_SETTING) {
                if (count($settings) === 1) {
                    $arguments = [$settings[0]];
                } else {
                    $arguments = [$settings];
                }
                $expr = ConfigConverterTools::addMethodCall(
                    self::IMPORT_FUNCTION,
                    $arguments,
                    $expr
                );
            } else {
                $profile = new Profile($settingsName, $settings ?? []);
                $expr = ConfigConverterTools::addMethodCall(
                    self::PROFILE_FUNCTION,
                    [$profile->toPhpExpr()],
                    $expr
                );
            }
            unset($this->settings[$settingsName]);
        }

        return $expr;
    }
}

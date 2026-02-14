<?php

declare(strict_types=1);

namespace Behat\Config;

use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;

use function is_string;

final class Config implements ConfigInterface
{
    public const IMPORTS_SETTING = 'imports';
    private const PREFERRED_PROFILE_NAME_SETTING = 'preferredProfileName';

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
}

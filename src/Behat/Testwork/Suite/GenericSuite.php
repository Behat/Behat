<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite;

use Behat\Testwork\Suite\Exception\ParameterNotFoundException;

/**
 * Represents generic (no specific attributes) test suite.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class GenericSuite implements Suite
{
    /**
     * Initializes suite.
     *
     * @param string $name
     */
    public function __construct(
        private $name,
        private array $settings,
    ) {
    }

    /**
     * Returns unique suite name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns suite settings.
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Checks if a setting with provided name exists.
     *
     * @param string $key
     */
    public function hasSetting($key): bool
    {
        return array_key_exists($key, $this->settings);
    }

    /**
     * Returns setting value by its key.
     *
     * @param string $key
     *
     * @throws ParameterNotFoundException If setting is not set
     */
    public function getSetting($key)
    {
        if (!$this->hasSetting($key)) {
            throw new ParameterNotFoundException(sprintf(
                '`%s` suite does not have a `%s` setting.',
                $this->getName(),
                $key
            ), $this->getName(), $key);
        }

        return $this->settings[$key];
    }
}

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
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $settings = array();

    /**
     * Initializes suite.
     *
     * @param string $name
     * @param array  $settings
     */
    public function __construct($name, array $settings)
    {
        $this->name = $name;
        $this->settings = $settings;
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
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Checks if a setting with provided name exists.
     *
     * @param string $key
     *
     * @return Boolean
     */
    public function hasSetting($key)
    {
        return isset($this->settings[$key]);
    }

    /**
     * Returns setting value by its key.
     *
     * @param string $key
     *
     * @return mixed
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

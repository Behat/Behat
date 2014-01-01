<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Suite\Exception;

/**
 * Testwork suite parameter not found exception.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ParameterNotFoundException extends SuiteException
{
    /**
     * @var string
     */
    private $parameter;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $name
     * @param string $parameter
     */
    public function __construct($message, $name, $parameter)
    {
        $this->parameter = $parameter;

        parent::__construct($message, $name);
    }

    /**
     * Returns parameter that caused exception.
     *
     * @return string
     */
    public function getParameter()
    {
        return $this->parameter;
    }
}

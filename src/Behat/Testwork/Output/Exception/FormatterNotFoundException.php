<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Exception;

use InvalidArgumentException;

/**
 * Represents an exception thrown because requested formatter is not found.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FormatterNotFoundException extends InvalidArgumentException implements OutputException
{
    /**
     * @var string
     */
    private $name;

    /**
     * Initializes exception.
     *
     * @param string $message
     * @param string $name
     */
    public function __construct($message, $name)
    {
        parent::__construct($message);
    }

    /**
     * Returns formatter name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}

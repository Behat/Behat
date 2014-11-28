<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Exception;

use Behat\Testwork\Tester\Context\Context;
use RuntimeException;

/**
 * Represents an exception caused by a wrong context instance being used.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongContextException extends RuntimeException implements TesterException
{
    /**
     * @var Context
     */
    private $context;

    /**
     * Initializes exception.
     *
     * @param string  $message
     * @param Context $context
     */
    public function __construct($message, Context $context)
    {
        parent::__construct($message);

        $this->context = $context;
    }

    /**
     * Returns context that caused exception.
     *
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }
}

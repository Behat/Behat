<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\EventDispatcher\Exception;

use Behat\Testwork\Hook\Exception\EventException;
use Behat\Testwork\Tester\Context\Context;
use LogicException;

/**
 * Represents an exception caused by an unsupported test context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class UnsupportedContextException extends LogicException implements EventException
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

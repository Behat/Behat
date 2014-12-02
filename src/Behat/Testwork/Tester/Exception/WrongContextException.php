<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Exception;

use Behat\Testwork\Tester\Context\TestContext;
use RuntimeException;

/**
 * Represents an exception caused by a wrong context instance being used.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class WrongContextException extends RuntimeException implements TesterException
{
    /**
     * @var TestContext
     */
    private $context;

    /**
     * Initializes exception.
     *
     * @param string  $message
     * @param TestContext $context
     */
    public function __construct($message, TestContext $context)
    {
        parent::__construct($message);

        $this->context = $context;
    }

    /**
     * Returns context that caused exception.
     *
     * @return TestContext
     */
    public function getContext()
    {
        return $this->context;
    }
}

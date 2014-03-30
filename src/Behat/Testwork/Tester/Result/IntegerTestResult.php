<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

/**
 * Represents an integer test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class IntegerTestResult implements TestResult
{
    /**
     * @var integer
     */
    private $resultCode;

    /**
     * Initializes test result.
     *
     * @param integer $resultCode
     */
    public function __construct($resultCode)
    {
        $this->resultCode = $resultCode;
    }

    /**
     * {@inheritdoc}
     */
    public function isPassed()
    {
        return self::PASSED == $this->getResultCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }
}

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Tester\Result;

/**
 * Testwork test result.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TestResult
{
    const PASSED = 0;
    const SKIPPED = 10;
    const PENDING = 20;
    const FAILED = 99;

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
     * Converts result code into a string representation.
     *
     * @param integer $resultCode
     *
     * @return string
     */
    public static function codeToString($resultCode)
    {
        switch ($resultCode) {
            case self::SKIPPED:
                return 'skipped';
            case self::PENDING:
                return 'pending';
            case self::FAILED:
                return 'failed';
        }

        return 'passed';
    }

    /**
     * Returns tester result code.
     *
     * @return integer
     */
    public function getResultCode()
    {
        return $this->resultCode;
    }

    /**
     * Converts test result into string.
     *
     * @return string
     */
    public function __toString()
    {
        return static::codeToString($this->getResultCode());
    }
}

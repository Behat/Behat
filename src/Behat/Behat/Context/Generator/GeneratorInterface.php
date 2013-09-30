<?php

namespace Behat\Behat\Context\Generator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Suite\SuiteInterface;

/**
 * Context generator interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * Checks if generator supports specific suite and context class.
     *
     * @param SuiteInterface $suite
     * @param string         $classname
     *
     * @return Boolean
     */
    public function supports(SuiteInterface $suite, $classname);

    /**
     * Generates context class code.
     *
     * @param SuiteInterface $suite
     * @param string         $classname
     *
     * @return string
     */
    public function generate(SuiteInterface $suite, $classname);

    /**
     * Returns priority of generator.
     *
     * @return integer
     */
    public function getPriority();
}

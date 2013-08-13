<?php

namespace Behat\Behat\Suite\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Suite\SuiteInterface;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\Event;

/**
 * Suites carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SuitesCarrierEvent extends Event implements EventInterface
{
    /**
     * @var SuiteInterface[]
     */
    private $suites = array();

    /**
     * Adds a suite.
     *
     * @param SuiteInterface $suite
     */
    public function addSuite(SuiteInterface $suite)
    {
        $this->suites[$suite->getName()] = $suite;
    }

    /**
     * Checks if suite with provided name exists.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function hasSuite($name)
    {
        return isset($this->suites[$name]);
    }

    /**
     * Returns suite by name.
     *
     * @param string $name
     *
     * @return SuiteInterface
     *
     * @throws InvalidArgumentException If suite with specified name not found
     */
    public function getSuite($name)
    {
        if (!$this->hasSuite($name)) {
            throw new InvalidArgumentException(sprintf(
                'Suite "%s" is not registered.',
                $name
            ));
        }

        return $this->suites[$name];
    }

    /**
     * Returns all added suites.
     *
     * @return SuiteInterface[]
     */
    public function getSuites()
    {
        return $this->suites;
    }
}

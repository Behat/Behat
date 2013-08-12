<?php

namespace Behat\Behat\Tester\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base tester carrier event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class TesterCarrierEvent extends Event implements EventInterface
{
    private $tester;

    /**
     * Check if carrier actually has a tester.
     *
     * @return Boolean
     */
    public function hasTester()
    {
        return null !== $this->tester;
    }

    /**
     * Returns tester (if has one).
     *
     * @return mixed
     */
    public function getTester()
    {
        return $this->tester;
    }

    /**
     * Sets tester.
     *
     * @param mixed $tester
     */
    public function setTester($tester)
    {
        $this->tester = $tester;
    }
}

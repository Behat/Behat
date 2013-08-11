<?php

namespace Behat\Behat\Event;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Suite\SuiteInterface;

/**
 * Testers lifecycle event.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface LifecycleEventInterface extends EventInterface
{
    /**
     * Returns suite instance.
     *
     * @return SuiteInterface
     */
    public function getSuite();

    /**
     * Returns context pool instance.
     *
     * @return ContextPoolInterface
     */
    public function getContextPool();
}

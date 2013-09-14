<?php

namespace Behat\Behat\Hook\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;

/**
 * BeforeSuite hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BeforeSuite extends SuiteHook
{
    /**
     * Initializes hook.
     *
     * @param Callable    $callback
     * @param null|string $description
     */
    public function __construct($callback, $description = null)
    {
        parent::__construct(EventInterface::HOOKABLE_BEFORE_SUITE, $callback, $description);
    }

    /**
     * Returns hook name.
     *
     * @return string
     */
    public function getName()
    {
        return 'BeforeSuite';
    }
}

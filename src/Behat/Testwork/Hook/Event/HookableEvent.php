<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Hook\Event;

use Behat\Testwork\Call\CallResults;
use Behat\Testwork\Environment\Environment;

/**
 * Hookable lifecycle event.
 *
 * * All hookable tester events should extend this class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class HookableEvent extends LifecycleEvent
{
    /**
     * @var null|CallResults
     */
    private $hookCallResults;

    /**
     * Initializes scenario event.
     *
     * @param Environment      $environment
     * @param null|CallResults $hookCallResults
     */
    public function __construct(Environment $environment, CallResults $hookCallResults = null)
    {
        parent::__construct($environment);

        $this->hookCallResults = $hookCallResults;
    }

    /**
     * Returns hook call results.
     *
     * @return null|CallResults
     */
    public function getHookCallResults()
    {
        return $this->hookCallResults;
    }
}

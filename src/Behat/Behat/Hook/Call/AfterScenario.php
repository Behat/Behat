<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Tester\Event\ScenarioTested;

/**
 * After scenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AfterScenario extends RuntimeScenarioHook
{
    /**
     * Initializes hook.
     *
     * @param null|string $filterString
     * @param Callable    $callback
     * @param null|string $description
     */
    public function __construct($filterString, $callback, $description = null)
    {
        parent::__construct(ScenarioTested::AFTER, $filterString, $callback, $description);
    }

    /**
     * Returns hook name.
     *
     * @return string
     */
    public function getName()
    {
        return 'AfterScenario';
    }
}

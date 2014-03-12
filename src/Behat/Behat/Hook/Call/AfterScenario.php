<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\Call;

use Behat\Behat\Hook\Scope\ScenarioScope;

/**
 * Represents an AfterScenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class AfterScenario extends RuntimeScenarioHook
{
    /**
     * Initializes hook.
     *
     * @param null|string $filterString
     * @param callable    $callable
     * @param null|string $description
     */
    public function __construct($filterString, $callable, $description = null)
    {
        parent::__construct(ScenarioScope::AFTER, $filterString, $callable, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'AfterScenario';
    }
}

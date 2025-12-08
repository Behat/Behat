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
 * Represents a BeforeScenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class BeforeScenario extends RuntimeScenarioHook
{
    /**
     * Initializes hook.
     *
     * @param string|null $filterString
     * @param callable    $callable
     * @param string|null $description
     */
    public function __construct($filterString, $callable, $description = null)
    {
        parent::__construct(ScenarioScope::BEFORE, $filterString, $callable, $description);
    }

    public function getName(): string
    {
        return 'BeforeScenario';
    }
}

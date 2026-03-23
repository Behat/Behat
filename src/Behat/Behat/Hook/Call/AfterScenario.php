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
use Behat\Testwork\Call\RuntimeCallee;

/**
 * Represents an AfterScenario hook.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @phpstan-import-type TBehatCallable from RuntimeCallee
 */
final class AfterScenario extends RuntimeScenarioHook
{
    /**
     * Initializes hook.
     *
     * @param string|null $filterString
     *
     * @phpstan-param TBehatCallable $callable
     */
    public function __construct($filterString, callable|array $callable, ?string $description = null)
    {
        parent::__construct(ScenarioScope::AFTER, $filterString, $callable, $description);
    }

    public function getName(): string
    {
        return 'AfterScenario';
    }
}

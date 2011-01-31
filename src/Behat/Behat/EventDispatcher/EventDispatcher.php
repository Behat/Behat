<?php

namespace Behat\Behat\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Event dispatcher.
 * Dispatches custom Behat events to hook with.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class EventDispatcher extends BaseEventDispatcher
{
    public function registerStatisticsCollector($collector)
    {
        $this->connect('suite.before',              array($collector, 'beforeSuite'),               0);
        $this->connect('suite.after',               array($collector, 'afterSuite'),                0);
        $this->connect('scenario.after',            array($collector, 'afterScenario'),             0);
        $this->connect('outline.example.after',     array($collector, 'afterOutlineExample'),       0);
        $this->connect('step.after',                array($collector, 'afterStep'),                 0);
    }

    public function registerHooksContainer($hooksContainer)
    {
        // TODO: optimize
        $this->connect('suite.before',              array($hooksContainer, 'fireSuiteHooks'),       10);
        $this->connect('suite.after',               array($hooksContainer, 'fireSuiteHooks'),       10);
        $this->connect('feature.before',            array($hooksContainer, 'fireFeatureHooks'),     10);
        $this->connect('feature.after',             array($hooksContainer, 'fireFeatureHooks'),     10);
        $this->connect('scenario.before',           array($hooksContainer, 'fireScenarioHooks'),    10);
        $this->connect('scenario.after',            array($hooksContainer, 'fireScenarioHooks'),    10);
        $this->connect('outline.example.before',    array($hooksContainer, 'fireScenarioHooks'),    10);
        $this->connect('outline.example.after',     array($hooksContainer, 'fireScenarioHooks'),    10);
        $this->connect('step.before',               array($hooksContainer, 'fireStepHooks'),        10);
        $this->connect('step.after',                array($hooksContainer, 'fireStepHooks'),        10);
    }

    public function registerFormatter($formatter)
    {
        $this->connect('suite.before',              array($formatter, 'beforeSuite'),               -10);
        $this->connect('suite.after',               array($formatter, 'afterSuite'),                -10);
        $this->connect('feature.before',            array($formatter, 'beforeFeature'),             -10);
        $this->connect('feature.after',             array($formatter, 'afterFeature'),              -10);
        $this->connect('background.before',         array($formatter, 'beforeBackground'),          -10);
        $this->connect('background.after',          array($formatter, 'afterBackground'),           -10);
        $this->connect('outline.before',            array($formatter, 'beforeOutline'),             -10);
        $this->connect('outline.example.before',    array($formatter, 'beforeOutlineExample'),      -10);
        $this->connect('outline.example.after',     array($formatter, 'afterOutlineExample'),       -10);
        $this->connect('outline.after',             array($formatter, 'afterOutline'),              -10);
        $this->connect('scenario.before',           array($formatter, 'beforeScenario'),            -10);
        $this->connect('scenario.after',            array($formatter, 'afterScenario'),             -10);
        $this->connect('step.before',               array($formatter, 'beforeStep'),                -10);
        $this->connect('step.after',                array($formatter, 'afterStep'),                 -10);
    }
}

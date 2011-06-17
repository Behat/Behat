<?php

namespace Behat\Behat\Hook\Loader;

use Behat\Behat\Hook\HookDispatcher,
    Behat\Behat\Hook\Annotation\BeforeSuite,
    Behat\Behat\Hook\Annotation\AfterSuite,
    Behat\Behat\Hook\Annotation\BeforeFeature,
    Behat\Behat\Hook\Annotation\AfterFeature,
    Behat\Behat\Hook\Annotation\BeforeScenario,
    Behat\Behat\Hook\Annotation\AfterScenario,
    Behat\Behat\Hook\Annotation\BeforeStep,
    Behat\Behat\Hook\Annotation\AfterStep;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Closured hook definitions loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ClosuredHookLoader implements HookLoaderInterface
{
    /**
     * Hook dispatcher.
     *
     * @var     Behat\Behat\Hook\HookDispatcher
     */
    private $dispatcher;

    /**
     * Initializes loader.
     *
     * @param   Behat\Behat\Hook\HookDispatcher $dispatcher definition dispatcher
     */
    public function __construct(HookDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @see     Behat\Behat\Hook\Loader\HookLoaderInterface::load()
     */
    public function load($resource)
    {
        $hooks = $this;

        require($resource);
    }

    /**
     * Hooks into "suite.before".
     *
     * @param   Callback    $callback   hook callback
     */
    public function beforeSuite($callback)
    {
        $this->dispatcher->addHook(new BeforeSuite($callback));
    }

    /**
     * Hooks into "suite.after".
     *
     * @param   Callback    $callback   hook callback
     */
    public function afterSuite($callback)
    {
        $this->dispatcher->addHook(new AfterSuite($callback));
    }

    /**
     * Hooks into "feature.before".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function beforeFeature($filter, $callback)
    {
        $this->dispatcher->addHook(new BeforeFeature($callback, '' !== $filter ? $filter : null));
    }

    /**
     * Hooks into "feature.after".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function afterFeature($filter, $callback)
    {
        $this->dispatcher->addHook(new AfterFeature($callback, '' !== $filter ? $filter : null));
    }

    /**
     * Hooks into "scenario.before" OR "outline.example.before".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function beforeScenario($filter, $callback)
    {
        $this->dispatcher->addHook(new BeforeScenario($callback, '' !== $filter ? $filter : null));
    }

    /**
     * Hooks into "scenario.after" OR "outline.example.after".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function afterScenario($filter, $callback)
    {
        $this->dispatcher->addHook(new AfterScenario($callback, '' !== $filter ? $filter : null));
    }

    /**
     * Hooks into "step.before".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function beforeStep($filter, $callback)
    {
        $this->dispatcher->addHook(new BeforeStep($callback, '' !== $filter ? $filter : null));
    }

    /**
     * Hooks into "step.after".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function afterStep($filter, $callback)
    {
        $this->dispatcher->addHook(new AfterStep($callback, '' !== $filter ? $filter : null));
    }
}

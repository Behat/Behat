<?php

namespace Behat\Behat\Hook\Loader;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP-files hooks loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PhpFileLoader implements LoaderInterface
{
    /**
     * Loaded hooks
     *
     * @var     array
     */
    protected $hooks = array();

    /**
     * {@inheritdoc}
     */
    public function load($resource)
    {
        $this->hooks = array(
            'suite.before'      => array(),
            'suite.after'       => array(),
            'feature.before'    => array(),
            'feature.after'     => array(),
            'scenario.before'   => array(),
            'scenario.after'    => array(),
            'step.before'       => array(),
            'step.after'        => array()
        );
        $hooks = $this;

        require_once $resource;

        return $this->hooks;
    }

    /**
     * Hooks into "suite.before".
     *
     * @param   Callback    $callback   hook callback
     */
    public function beforeSuite($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['suite.before'][] = $callback;
    }

    /**
     * Hooks into "suite.after".
     *
     * @param   Callback    $callback   hook callback
     */
    public function afterSuite($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['suite.after'][] = $callback;
    }

    /**
     * Hooks into "feature.before".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function beforeFeature($filter, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['feature.before'][] = array($filter, $callback);
    }

    /**
     * Hooks into "feature.after".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function afterFeature($filter, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['feature.after'][] = array($filter, $callback);
    }

    /**
     * Hooks into "scenario.before" OR "outline.example.before".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function beforeScenario($filter, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['scenario.before'][] = array($filter, $callback);
    }

    /**
     * Hooks into "scenario.after" OR "outline.example.after".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function afterScenario($filter, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['scenario.after'][] = array($filter, $callback);
    }

    /**
     * Hooks into "step.before".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function beforeStep($filter, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['step.before'][] = array($filter, $callback);
    }

    /**
     * Hooks into "step.after".
     *
     * @param   string      $filter     filter string (tags or name)
     * @param   Callback    $callback   hook callback
     */
    public function afterStep($filter, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('You should provide valid callable as hook argument');
        }

        $this->hooks['step.after'][] = array($filter, $callback);
    }
}

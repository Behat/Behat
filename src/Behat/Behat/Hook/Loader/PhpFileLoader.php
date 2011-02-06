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
 * Plain PHP Files Hooks Loader.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PhpFileLoader implements LoaderInterface
{
    protected $hooks = array();

    /**
     * Load hooks from file. 
     * 
     * @param   string          $path       plain php file path
     * @return  array                       array of hooks
     */
    public function load($path)
    {
        $this->hooks = array(
            'suite.before'      => array()
          , 'suite.after'       => array()
          , 'feature.before'    => array()
          , 'feature.after'     => array()
          , 'scenario.before'   => array()
          , 'scenario.after'    => array()
          , 'step.before'       => array()
          , 'step.after'        => array()
        );
        $hooks = $this;

        require_once($path);

        return $this->hooks;
    }

    /**
     * Hook Before Suite Run.
     * 
     * @param   callback    $callback   hook callback
     */
    public function beforeSuite($callback)
    {
        $this->hooks['suite.before'][] = $callback;
    }

    /**
     * Hook After Suite Run.
     * 
     * @param   callback    $callback   hook callback
     */
    public function afterSuite($callback)
    {
        $this->hooks['suite.after'][] = $callback;
    }

    /**
     * Hook Before Feature Run. 
     * 
     * @param   string      $filter     filter string (tags or name)
     * @param   callback    $callback   hook callback
     */
    public function beforeFeature($filter, $callback)
    {
        $this->hooks['feature.before'][] = array($filter, $callback);
    }

    /**
     * Hook After Feature Run. 
     * 
     * @param   string      $filter     filter string (tags or name)
     * @param   callback    $callback   hook callback
     */
    public function afterFeature($filter, $callback)
    {
        $this->hooks['feature.after'][] = array($filter, $callback);
    }

    /**
     * Hook Before Scenario Run. 
     * 
     * @param   string      $filter     filter string (tags or name)
     * @param   callback    $callback   hook callback
     */
    public function beforeScenario($filter, $callback)
    {
        $this->hooks['scenario.before'][] = array($filter, $callback);
    }

    /**
     * Hook After Scenario Run. 
     * 
     * @param   string      $filter     filter string (tags or name)
     * @param   callback    $callback   hook callback
     */
    public function afterScenario($filter, $callback)
    {
        $this->hooks['scenario.after'][] = array($filter, $callback);
    }

    /**
     * Hook Before Step Run. 
     * 
     * @param   mixed       $filter     filter string (tags or name)
     * @param   callback    $callback   hook callback
     */
    public function beforeStep($filter, $callback)
    {
        $this->hooks['step.before'][] = array($filter, $callback);
    }

    /**
     * Hook After Step Run.
     *
     * @param   mixed       $filter     filter string (tags or name)
     * @param   callback    $callback   hook callback
     */
    public function afterStep($filter, $callback)
    {
        $this->hooks['step.after'][] = array($filter, $callback);
    }
}

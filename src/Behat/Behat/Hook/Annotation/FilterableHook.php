<?php

namespace Behat\Behat\Hook\Annotation;

use Behat\Behat\Event\EventInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base filterable hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class FilterableHook extends Hook
{
    private $filter;

    /**
     * Initializes hook.
     *
     * @param callback $callback     callback
     * @param string   $filterString hook filter
     */
    public function __construct($callback, $filterString = null)
    {
        parent::__construct($callback);

        $this->filter = $filterString;
    }

    /**
     * Returns filter string.
     *
     * @return stirng
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Checks that current hook matches provided event object.
     *
     * @param EventInterface $event
     *
     * @return Boolean
     */
    abstract public function filterMatches(EventInterface $event);
}

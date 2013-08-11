<?php

namespace Behat\Behat\Hook\Callee;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Hook\FilterableHookInterface;

/**
 * Base FilterableHook hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class FilterableHook extends Hook implements FilterableHookInterface
{
    /**
     * @var null|string
     */
    private $filterString;

    /**
     * Initializes hook.
     *
     * @param string      $eventName
     * @param null|string $filterString
     * @param Callable    $callback
     * @param null|string $description
     */
    public function __construct($eventName, $filterString, $callback, $description = null)
    {
        $this->filterString = $filterString;

        parent::__construct($eventName, $callback, $description);
    }

    /**
     * Returns hook filter string (if has one).
     *
     * @return null|string
     */
    public function getFilterString()
    {
        return $this->filterString;
    }
}

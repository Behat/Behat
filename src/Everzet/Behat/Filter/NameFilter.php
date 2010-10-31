<?php

namespace Everzet\Behat\Filter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/*
 * This file is part of the Behat.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Filters scenarios by feature/scenario name.
 *
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 */
class NameFilter implements FilterInterface
{
    protected $filterString;

    /**
     * Set filtering string.
     * 
     * @param   string  $tags   tags filter string
     */
    public function setFilterString($filterString)
    {
        $this->filterString = trim($filterString);
    }

    /**
     * @see     Everzet\Behat\Filter\FilterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.run.filter_scenarios', array($this, 'filterScenarios'));
    }

    /**
     * Filter scenarios by name.
     *
     * @param   Event   $event      filter event
     * @param   array   $scenarios  scenarios
     * 
     * @return  array               filtered scenarios
     */
    public function filterScenarios(Event $event, array $scenarios)
    {
        if (!empty($this->filterString)) {
            $feature            = $event->getSubject();
            $filteredScenarios  = array();

            foreach ($scenarios as $scenario) {
                $satisfies = false;

                if ('/' === $this->filterString[0]) {
                    $satisfies = preg_match($this->filterString, $scenario->getTitle())
                        || preg_match($this->filterString, $feature->getTitle());
                } else {
                    $satisfies = false !== mb_strpos($scenario->getTitle(), $this->filterString)
                        || false !== mb_strpos($feature->getTitle(), $this->filterString);
                }

                if ($satisfies) {
                    $filteredScenarios[] = $scenario;
                }
            }

            return $filteredScenarios;
        }

        return $scenarios;
    }
}

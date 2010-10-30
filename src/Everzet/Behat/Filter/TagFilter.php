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
 * Filters scenarios by feature/scenario tag.
 *
 * @author     Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TagFilter implements FilterInterface
{
    protected $filterString;

    /**
     * Set filtering string.
     * 
     * @param   string  $tags   tags filter string
     */
    public function setFilterString($filterString)
    {
        $this->filterString = $filterString;
    }

    /**
     * @see     Everzet\Behat\Filter\FilterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.run.filter_scenarios', array($this, 'filterScenarios'));
    }

    /**
     * Filter scenarios by tags.
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
                $satisfies = true;

                foreach (explode('&&', $this->filterString) as $andTags) {
                    $satisfiesComma = false;

                    foreach (explode(',', $andTags) as $tag) {
                        $tag = preg_replace('/\@/', '', trim($tag));

                        if ('~' === $tag[0]) {
                            $tag = mb_substr($tag, 1);
                            $satisfiesComma = (!$scenario->hasTag($tag) && !$feature->hasTag($tag)) || $satisfiesComma;
                        } else {
                            $satisfiesComma = ($scenario->hasTag($tag) || $feature->hasTag($tag)) || $satisfiesComma;
                        }
                    }

                    $satisfies = (false !== $satisfiesComma && $satisfies && $satisfiesComma) || false;
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

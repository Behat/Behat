<?php

namespace Everzet\Behat\Filter;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;

/*
 * This file is part of the behat.
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
    protected $tags;

    /**
     * Constructs filter
     *
     * @param   Container   $container  dependency container
     */
    public function __construct(Container $container)
    {
        $this->tags = $container->getParameter('filter.tags');
    }

    /**
     * Registers listeners on filter
     *
     * @see     Everzet\Behat\Filter\FilterInterface
     */
    public function registerListeners(EventDispatcher $dispatcher)
    {
        $dispatcher->connect('feature.run.filter_scenarios', array($this, 'filterScenarios'));
    }

    /**
     * Filtering scenarios by tags
     *
     * @param   Event   $event              filter event
     * @param   array   $scenarioRunners    scenario runners
     * 
     * @return  array                       filtered scenario runners
     */
    public function filterScenarios(Event $event, array $scenarios)
    {
        $filteredScenarios = array();

        if ($this->tags) {
            $tags = explode(',', $this->tags);

            foreach ($scenarios as $scenario) {
                $feature    = $scenario->getFeature();
                $satisfies  = false;

                foreach ($tags as $tag) {
                    $tag = trim($tag);

                    if ('~' === $tag[0]) {
                        $tag = mb_substr($tag, 2);

                        if (!$scenario->hasTag($tag) && !$feature->hasTag($tag)) {
                            $satisfies = true;
                        }
                    } else {
                        $tag = mb_substr($tag, 1);

                        if ($scenario->hasTag($tag) || $feature->hasTag($tag)) {
                            $satisfies = true;
                        }
                    }
                }

                if ($satisfies) {
                    $filteredScenarios[] = $scenario;
                }
            }
        } else {
            $filteredScenarios = $scenarios;
        }

        return $filteredScenarios;
    }
}

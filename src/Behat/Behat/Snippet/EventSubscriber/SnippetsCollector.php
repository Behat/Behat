<?php

namespace Behat\Behat\Snippet\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Snippet\ContextSnippetInterface;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Gherkin\Node\StepNode;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Snippets collector.
 * Collects all created snippets.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetsCollector implements EventSubscriberInterface
{
    /**
     * @var SnippetInterface[string]
     */
    private $snippets = array();
    /**
     * @var StepNode[string]
     */
    private $snippetSteps = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::AFTER_STEP => array('collectSnippet', -10));
    }

    /**
     * Collects snippet after step.
     *
     * @param StepEvent $event
     */
    public function collectSnippet(StepEvent $event)
    {
        if (StepEvent::UNDEFINED !== $event->getResult()) {
            return;
        }
        if (!$event->hasSnippet()) {
            return;
        }

        $snippet = $event->getSnippet();
        $hash = $snippet->getHash();

        if (!isset($this->snippets[$hash])) {
            $this->snippets[$hash] = $snippet;
            $this->snippetSteps[$hash] = array();
        }

        $this->snippetSteps[$hash][] = $event->getStep();

        if (!$snippet instanceof ContextSnippetInterface) {
            return;
        }
        $savedSnippet = $this->snippets[$hash];
        if (!$savedSnippet instanceof ContextSnippetInterface) {
            return;
        }

        $contextClasses = array_merge($savedSnippet->getContextClasses(), $snippet->getContextClasses());
        $savedSnippet->setContextClasses(array_unique($contextClasses));
    }

    /**
     * Check if some snippet been collected.
     *
     * @return Boolean
     */
    public function hasSnippets()
    {
        return count($this->snippets) > 0;
    }

    /**
     * Returns hash of definition snippets for undefined steps.
     *
     * @return SnippetInterface[string]
     */
    public function getSnippets()
    {
        return $this->snippets;
    }

    /**
     * Returns list of steps that need this exact snippet.
     *
     * @param SnippetInterface $snippet
     *
     * @return StepNode[]
     */
    public function getStepsThatNeed(SnippetInterface $snippet)
    {
        return $this->snippetSteps[$snippet->getHash()];
    }
}

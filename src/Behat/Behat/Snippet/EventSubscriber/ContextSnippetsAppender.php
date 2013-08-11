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
use Behat\Behat\Event\ExerciseEvent;
use Behat\Behat\Snippet\ContextSnippet;
use ReflectionClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context snippets appender.
 * Appends context snippets to appropriate contexts.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ContextSnippetsAppender implements EventSubscriberInterface
{
    /**
     * @var SnippetsCollector
     */
    private $snippets;
    /**
     * @var Boolean
     */
    private $enabled = false;

    /**
     * Initializes appender.
     *
     * @param SnippetsCollector $snippets
     * @param Boolean           $enabled
     */
    public function __construct(SnippetsCollector $snippets, $enabled = false)
    {
        $this->snippets = $snippets;
        $this->enabled = (bool)$enabled;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::AFTER_EXERCISE => 'appendSnippets');
    }

    /**
     * Enables appender.
     *
     * @param Boolean $enable
     */
    public function enable($enable = true)
    {
        $this->enabled = (bool)$enable;
    }

    /**
     * Appends snippets to appropriate contexts.
     *
     * @param ExerciseEvent $event
     */
    public function appendSnippets(ExerciseEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        foreach ($this->snippets->getSnippets() as $snippet) {
            if (!$snippet instanceof ContextSnippet) {
                continue;
            }

            foreach ($snippet->getContextClasses() as $contextClass) {
                $reflection = new ReflectionClass($contextClass);
                $generated = rtrim(strtr($snippet->getSnippet(), array('\\' => '\\\\', '$' => '\\$')));
                $content = file_get_contents($reflection->getFileName());
                $content = preg_replace('/}[ \n]*$/', "\n" . $generated . "\n}\n", $content);

                file_put_contents($reflection->getFileName(), $content);
            }
        }
    }
}

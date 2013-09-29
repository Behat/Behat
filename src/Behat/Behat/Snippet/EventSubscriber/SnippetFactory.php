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
use Behat\Behat\Snippet\Event\SnippetCarrierEvent;
use Behat\Behat\Snippet\Generator\GeneratorInterface;
use Behat\Behat\Snippet\Util\Transliterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context snippet generator.
 * Generates snippets for non-empty context pools.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SnippetFactory implements EventSubscriberInterface
{
    /**
     * @var GeneratorInterface[]
     */
    private $generators = array();

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::CREATE_SNIPPET => array('createSnippet', 0));
    }

    /**
     * Registers snippet generator.
     *
     * @param GeneratorInterface $generator
     */
    public function registerGenerator(GeneratorInterface $generator)
    {
        $this->generators[] = $generator;
    }

    /**
     * Generate snippet and set it to the event.
     *
     * @param SnippetCarrierEvent $event
     */
    public function createSnippet(SnippetCarrierEvent $event)
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($event->getSuite(), $event->getContextPool(), $event->getStep())) {
                $snippet = $generator->generate($event->getSuite(), $event->getContextPool(), $event->getStep());
                $event->setSnippet($snippet);

                break;
            }
        }
    }
}

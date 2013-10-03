<?php

namespace Behat\Behat\Snippet\UseCase;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Definition\Event\DefinitionCarrierEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Snippet\Generator\GeneratorInterface;
use Behat\Behat\Snippet\RepositoryInterface;
use Behat\Behat\Snippet\Util\Transliterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context snippet generator.
 * Generates snippets for non-empty context pools.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class CreateSnippet implements EventSubscriberInterface
{
    /**
     * @var RepositoryInterface
     */
    private $repository;
    /**
     * @var GeneratorInterface[]
     */
    private $generators = array();

    /**
     * Initializes use case.
     *
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::FIND_DEFINITION => array('createSnippet', -999));
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
     * @param DefinitionCarrierEvent $event
     */
    public function createSnippet(DefinitionCarrierEvent $event)
    {
        if ($event->hasDefinition()) {
            return;
        }

        $suite = $event->getSuite();
        $contextPool = $event->getContextPool();
        $step = $event->getStep();

        foreach ($this->generators as $generator) {
            if ($generator->supports($suite, $contextPool, $step)) {
                $snippet = $generator->generate($suite, $contextPool, $step);
                $this->repository->registerSnippet($step, $snippet);

                return;
            }
        }
    }
}

<?php

namespace Behat\Behat\Definition\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Definition\Event\DefinitionCarrierEvent;
use Behat\Behat\Definition\Event\DefinitionsCarrierEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Exception\AmbiguousException;
use Behat\Behat\Suite\SuiteInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Definition finder.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionFinder extends DispatchingService implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Initializes definition finder.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param TranslatorInterface      $translator
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, TranslatorInterface $translator)
    {
        parent::__construct($eventDispatcher);
        $this->translator = $translator;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(EventInterface::FIND_DEFINITION => array('findDefinition', 0));
    }

    /**
     * Searches and assigns matched definition to the event.
     *
     * @param DefinitionCarrierEvent $event
     *
     * @throws AmbiguousException If multiple matching definitions found
     */
    public function findDefinition(DefinitionCarrierEvent $event)
    {
        if ($event->hasDefinition()) {
            return;
        }

        $step = $event->getStep();
        $text = $step->getText();
        $suite = $event->getSuite();
        $definitions = $this->getDefinitions($suite, $event->getContextPool());

        $matches = array();
        foreach ($definitions as $regex => $definition) {
            $trans = $this->translator->trans($regex, array(), $suite->getId(), $step->getLanguage());
            $trans = ($regex !== $trans) ? $trans : null;

            if (preg_match($regex, $text, $match) || ($trans && preg_match($trans, $text, $match))) {
                array_shift($match);
                $matches[] = $definition;
                $arguments = $this->prepareArguments($definition, $match, $step->getArguments());

                $event->setMatchedText($text);
                $event->setDefinition($definition);
                $event->setArguments($arguments);
            }
        }

        if (count($matches) > 1) {
            throw new AmbiguousException($text, $matches);
        }
    }

    /**
     * Returns all available definitions for suite & context pool.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     *
     * @return DefinitionInterface[]
     */
    private function getDefinitions(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $definitionsProvider = new DefinitionsCarrierEvent($suite, $contexts);
        $this->dispatch(EventInterface::LOAD_DEFINITIONS, $definitionsProvider);

        return $definitionsProvider->getDefinitions();
    }

    /**
     * Prepares definition arguments.
     *
     * @param DefinitionInterface $definition
     * @param array               $match
     * @param array               $multiline
     *
     * @return array
     */
    private function prepareArguments(DefinitionInterface $definition, array $match, array $multiline)
    {
        $arguments = array();
        foreach ($definition->getReflection()->getParameters() as $num => $parameter) {
            if (isset($match[$parameter->getName()])) {
                $arguments[] = $match[$parameter->getName()];
            } elseif (isset($match[$num])) {
                $arguments[] = $match[$num];
            }
        }
        foreach ($multiline as $argument) {
            $arguments[] = $argument;
        }

        return $arguments;
    }
}

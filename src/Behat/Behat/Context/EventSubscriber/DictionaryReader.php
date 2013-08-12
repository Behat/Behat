<?php

namespace Behat\Behat\Context\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Reader\CalleesReader;
use Behat\Behat\Definition\DefinitionInterface;
use Behat\Behat\Definition\Event\DefinitionsCarrierEvent;
use Behat\Behat\Hook\Event\HooksCarrierEvent;
use Behat\Behat\Transformation\Event\TransformationsCarrierEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Hook\HookInterface;
use Behat\Behat\Transformation\TransformationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context dictionary reader.
 * Loads hooks, definitions and transformations when asked by appropriate events.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DictionaryReader implements EventSubscriberInterface
{
    /**
     * @var CalleesReader
     */
    private $calleesReader;

    /**
     * Initializes reader.
     *
     * @param CalleesReader $calleesReader
     */
    public function __construct(CalleesReader $calleesReader)
    {
        $this->calleesReader = $calleesReader;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::LOAD_HOOKS           => array('loadHooks', 0),
            EventInterface::LOAD_DEFINITIONS     => array('loadDefinitions', 0),
            EventInterface::LOAD_TRANSFORMATIONS => array('loadTransformations', 0),
        );
    }

    /**
     * Loads contexts hooks into event.
     *
     * @param HooksCarrierEvent $event
     */
    public function loadHooks(HooksCarrierEvent $event)
    {
        foreach ($this->calleesReader->read($event->getSuite(), $event->getContextPool()) as $callback) {
            if ($callback instanceof HookInterface) {
                $event->addHook($callback);
            }
        }
    }

    /**
     * Loads contexts definitions into event.
     *
     * @param DefinitionsCarrierEvent $event
     */
    public function loadDefinitions(DefinitionsCarrierEvent $event)
    {
        foreach ($this->calleesReader->read($event->getSuite(), $event->getContextPool()) as $callback) {
            if ($callback instanceof DefinitionInterface) {
                $event->addDefinition($callback);
            }
        }
    }

    /**
     * Loads contexts transformations into event.
     *
     * @param TransformationsCarrierEvent $event
     */
    public function loadTransformations(TransformationsCarrierEvent $event)
    {
        foreach ($this->calleesReader->read($event->getSuite(), $event->getContextPool()) as $callback) {
            if ($callback instanceof TransformationInterface) {
                $event->addTransformation($callback);
            }
        }
    }
}

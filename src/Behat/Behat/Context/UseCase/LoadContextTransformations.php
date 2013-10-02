<?php

namespace Behat\Behat\Context\UseCase;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Reader\CalleesReader;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Transformation\Event\TransformationsCarrierEvent;
use Behat\Behat\Transformation\TransformationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Context transformations loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class LoadContextTransformations implements EventSubscriberInterface
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
        return array(EventInterface::LOAD_TRANSFORMATIONS => array('loadTransformations', 0));
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

<?php

namespace Behat\Behat\Transformation\EventSubscriber;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Callee\Event\ExecuteCalleeEvent;
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Definition\Event\ExecuteDefinitionEvent;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Transformation\Event\TransformationsCarrierEvent;
use Behat\Behat\Transformation\TransformationInterface;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Arguments transformer.
 * Transforms definition arguments before executing it.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ArgumentsTransformer extends DispatchingService implements EventSubscriberInterface
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
        return array(EventInterface::EXECUTE_DEFINITION => array('transformArguments', 50));
    }

    /**
     * Transforms definition execution arguments and sets them back to the event.
     *
     * @param ExecuteDefinitionEvent $event
     */
    public function transformArguments(ExecuteDefinitionEvent $event)
    {
        if (!$event->hasArguments()) {
            return;
        }

        $suite = $event->getSuite();
        $contexts = $event->getContextPool();
        $suiteId = $suite->getId();
        $language = $event->getStep()->getLanguage();
        $transformations = $this->getTransformations($suite, $contexts);

        $arguments = array();
        foreach ($event->getArguments() as $argument) {
            $subject = $this->getTransformationSubject($argument);

            foreach ($transformations as $regex => $transformation) {
                $trans = $this->translator->trans($regex, array(), $suiteId, $language);
                $trans = ($regex !== $trans) ? $trans : null;

                if (preg_match($regex, $subject, $match) || ($trans && preg_match($trans, $subject, $match))) {
                    array_shift($match);
                    $args = $argument instanceof TableNode ? array($argument) : $match;
                    $argument = $this->executeTransformation($suite, $contexts, $transformation, $args) ? : $argument;
                }
            }

            $arguments[] = $argument;
        }

        $event->setArguments($arguments);
    }

    /**
     * Returns transformation subject given the argument.
     *
     * @param mixed $argument
     *
     * @return string
     */
    private function getTransformationSubject($argument)
    {
        if ($argument instanceof TableNode) {
            return 'table:' . implode(',', $argument->getRow(0));
        }

        return (string)$argument;
    }

    /**
     * Returns all transformations available for provided suite and context pool.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     *
     * @return TransformationInterface[]
     */
    private function getTransformations(SuiteInterface $suite, ContextPoolInterface $contexts)
    {
        $transformationsProvider = new TransformationsCarrierEvent($suite, $contexts);
        $this->dispatch(EventInterface::LOAD_TRANSFORMATIONS, $transformationsProvider);

        return $transformationsProvider->getTransformations();
    }

    /**
     * Executes transformation.
     *
     * @param SuiteInterface          $suite
     * @param ContextPoolInterface    $contexts
     * @param TransformationInterface $transformation
     * @param array                   $arguments
     *
     * @return null|mixed
     */
    private function executeTransformation(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        TransformationInterface $transformation,
        array $arguments
    )
    {
        $execution = new ExecuteCalleeEvent($suite, $contexts, $transformation, $arguments);
        $this->dispatch(EventInterface::EXECUTE_TRANSFORMATION, $execution);

        return $execution->getReturn();
    }
}

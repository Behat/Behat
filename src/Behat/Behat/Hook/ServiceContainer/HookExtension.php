<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Hook\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Output\ServiceContainer\Formatter\PrettyFormatterFactory;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Hook\ServiceContainer\HookExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Behat hook extension.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class HookExtension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        parent::load($container, $config);
        $this->loadAnnotationReader($container);
        $this->loadOutputListeners($container);
    }

    /**
     * Loads hook annotation reader.
     *
     * @param ContainerBuilder $container
     */
    protected function loadAnnotationReader(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Hook\Context\Annotation\HookAnnotationReader');
        $definition->addTag(ContextExtension::ANNOTATION_READER_TAG, array('priority' => 50));
        $container->setDefinition(ContextExtension::ANNOTATION_READER_TAG . '.hook', $definition);
    }

    /**
     * Loads hooked events subscriber.
     *
     * @param ContainerBuilder $container
     */
    protected function loadHookedEventsSubscriber(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Hook\EventDispatcher\HookedEventsSubscriber', array(
            new Reference(self::DISPATCHER_ID),
            new Reference(EventDispatcherExtension::DISPATCHER_ID)
        ));
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG);
        $container->setDefinition(self::EVENT_SUBSCRIBER, $definition);
    }

    /**
     * Loads output listeners.
     *
     * @param ContainerBuilder $container
     */
    protected function loadOutputListeners(ContainerBuilder $container)
    {
        $definition = new Definition('Behat\Behat\Hook\Output\Node\EventListener\InPlaceHookListener', array(
            new Reference(PrettyFormatterFactory::PRETTY_ROOT_LISTENER_ID),
            new Definition('Behat\Behat\Hook\Output\Node\Printer\Pretty\PrettyHookPrinter', array(
                new Reference(ExceptionExtension::PRESENTER_ID)
            ))
        ));
        $definition->addTag(PrettyFormatterFactory::PRETTY_ROOT_LISTENER_WRAPPER_TAG);
        $container->setDefinition(PrettyFormatterFactory::PRETTY_ROOT_LISTENER_WRAPPER_TAG . '.hooks', $definition);
    }
}

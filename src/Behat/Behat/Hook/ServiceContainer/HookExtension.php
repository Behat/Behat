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
use Behat\Testwork\Hook\ServiceContainer\HookExtension as BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
}

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Autoloader\ServiceContainer;

use Behat\Testwork\Autoloader\ServiceContainer\AutoloaderExtension as BaseExtension;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Behat autoloader extension.
 *
 * Configures default context paths for Behat.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AutoloaderExtension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->beforeNormalization()
            ->ifString()
                ->then(function($path) {
                    return array('' => $path);
                })
            ->end()
            ->defaultValue(array('' => '%paths.base%/features/bootstrap'))
            ->treatTrueLike(array('' => '%paths.base%/features/bootstrap'))
            ->treatNullLike(array('' => '%paths.base%/features/bootstrap'))
            ->treatFalseLike(array())
            ->prototype('scalar')->end()
        ;
    }
}

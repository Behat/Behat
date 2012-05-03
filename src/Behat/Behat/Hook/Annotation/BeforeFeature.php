<?php

namespace Behat\Behat\Hook\Annotation;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * BeforeFeature hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BeforeFeature extends FeatureHook
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'beforeFeature';
    }
}

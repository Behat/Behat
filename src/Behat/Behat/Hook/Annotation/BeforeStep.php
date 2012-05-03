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
 * BeforeStep hook class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BeforeStep extends StepHook
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'beforeStep';
    }
}

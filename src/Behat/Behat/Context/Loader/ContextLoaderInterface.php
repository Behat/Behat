<?php

namespace Behat\Behat\Context\Loader;

use Behat\Behat\Context\ContextInterface;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context loader interface.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextLoaderInterface
{
    /**
     * Checks if loader supports provided context.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context
     */
    function supports(ContextInterface $context);

    /**
     * Loads definitions and translations from provided context.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context
     */
    function load(ContextInterface $context);
}

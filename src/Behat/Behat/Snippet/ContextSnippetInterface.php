<?php

namespace Behat\Behat\Snippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Context definition snippet interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface ContextSnippetInterface extends SnippetInterface
{
    /**
     * Sets snippet context classes.
     *
     * @param string[] $contextClasses
     */
    public function setContextClasses(array $contextClasses);

    /**
     * Returns array of context classes this snippet belongs to.
     *
     * @return string[]
     */
    public function getContextClasses();
}

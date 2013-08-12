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
 * Context interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SnippetInterface
{
    /**
     * Returns snippet unique hash (ignoring step type).
     *
     * @return string
     */
    public function getHash();

    /**
     * Returns definition snippet text.
     *
     * @return string
     */
    public function getSnippet();
}

<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet;

use Behat\Gherkin\Node\StepNode;

/**
 * Step definition snippet.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface Snippet
{
    /**
     * Returns snippet type.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns snippet unique ID (step type independent).
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

    /**
     * Returns step which asked for this snippet.
     *
     * @return StepNode
     */
    public function getStep();

    /**
     * Returns snippet target.
     *
     * @return string
     */
    public function getTarget();
}

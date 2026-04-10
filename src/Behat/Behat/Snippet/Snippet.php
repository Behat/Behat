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
     */
    public function getType(): string;

    /**
     * Returns snippet unique ID (step type independent).
     */
    public function getHash(): string;

    /**
     * Returns definition snippet text.
     */
    public function getSnippet(): string;

    /**
     * Returns step which asked for this snippet.
     */
    public function getStep(): StepNode;

    /**
     * Returns snippet target.
     */
    public function getTarget(): string;
}

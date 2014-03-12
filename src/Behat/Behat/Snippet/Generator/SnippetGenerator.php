<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Snippet\Generator;

use Behat\Behat\Snippet\Snippet;
use Behat\Behat\Snippet\SnippetRegistry;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Environment\Environment;

/**
 * Generates snippet for a specific step in a specific environment.
 *
 * @see SnippetRegistry
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface SnippetGenerator
{
    /**
     * Checks if generator supports search query.
     *
     * @param Environment $environment
     * @param StepNode    $step
     *
     * @return Boolean
     */
    public function supportsEnvironmentAndStep(Environment $environment, StepNode $step);

    /**
     * Generates snippet from search.
     *
     * @param Environment $environment
     * @param StepNode    $step
     *
     * @return Snippet
     */
    public function generateSnippet(Environment $environment, StepNode $step);
}

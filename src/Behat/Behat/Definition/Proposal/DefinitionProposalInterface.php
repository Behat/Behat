<?php

namespace Behat\Behat\Definition\Proposal;

use Behat\Gherkin\Node\StepNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Definition\DefinitionSnippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definition proposal interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface DefinitionProposalInterface
{
    /**
     * Checks if loader supports provided context.
     *
     * @param ContextInterface $context
     *
     * @return Boolean
     */
    public function supports(ContextInterface $context);

    /**
     * Loads definitions and translations from provided context.
     *
     * @param ContextInterface $context
     * @param StepNode         $step
     *
     * @return DefinitionSnippet
     */
    public function propose(ContextInterface $context, StepNode $step);
}

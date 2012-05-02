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
 * Definition proposals dispatcher.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionProposalDispatcher
{
    private $proposals = array();

    /**
     * Adds proposal object to the dispatcher.
     *
     * @param DefinitionProposalInterface $proposal
     */
    public function addProposal(DefinitionProposalInterface $proposal)
    {
        $this->proposals[] = $proposal;
    }

    /**
     * Returns step definition for step node.
     *
     * @param ContextInterface $context
     * @param StepNode         $step
     *
     * @return DefinitionSnippet
     */
    public function propose(ContextInterface $context, StepNode $step)
    {
        foreach ($this->proposals as $proposal) {
            if ($proposal->supports($context)) {
                return $proposal->propose($context, $step);
            }
        }
    }
}

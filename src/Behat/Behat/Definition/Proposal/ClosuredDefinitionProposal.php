<?php

namespace Behat\Behat\Definition\Proposal;

use Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Definition\DefinitionSnippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Closured definitions proposal.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ClosuredDefinitionProposal implements DefinitionProposalInterface
{
    /**
     * Checks if loader supports provided context.
     *
     * @param ContextInterface $context
     *
     * @return Boolean
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof ClosuredContextInterface;
    }

    /**
     * Loads definitions and translations from provided context.
     *
     * @param ContextInterface $context
     * @param StepNode         $step
     *
     * @return DefinitionSnippet
     */
    public function propose(ContextInterface $context, StepNode $step)
    {
        $text  = $step->getText();
        $regex = preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $text);
        $regex = preg_replace(
            array(
                "/(?<= |^)\\\'(?:((?!\\').)*)\\\'(?= |$)/", // Single quoted strings
                '/(?<= |^)\"(?:[^\"]*)\"(?= |$)/',          // Double quoted strings
                '/(\d+)/',                                  // Numbers
            ),
            array(
                "\\'([^\']*)\\'",
                "\"([^\"]*)\"",
                "(\\d+)",
            ),
            $regex
        );

        preg_match('/' . $regex . '/', $text, $matches);
        $count = count($matches) - 1;

        $args = array("\$world");
        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$arg".($i + 1);
        }

        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof PyStringNode) {
                $args[] = "\$string";
            } elseif ($argument instanceof TableNode) {
                $args[] = "\$table";
            }
        }

        $description = sprintf(<<<PHP
\$steps->%s('/^%s$/', function(%s) {
    throw new \Behat\Behat\Exception\PendingException();
});
PHP
          , '%s', str_replace('%', '%%', $regex), implode(', ', $args)
        );

        return new DefinitionSnippet($step, $description);
    }
}

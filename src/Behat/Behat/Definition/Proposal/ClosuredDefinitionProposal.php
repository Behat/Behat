<?php

namespace Behat\Behat\Definition\Proposal;

use Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Context\ClosuredContextInterface;

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
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ClosuredDefinitionProposal implements DefinitionProposalInterface
{
    /**
     * @see     Behat\Behat\Definition\Proposal\DefinitionProposalInterface::supports()
     */
    public function supports(ContextInterface $context)
    {
        return $context instanceof ClosuredContextInterface;
    }

    /**
     * @see     Behat\Behat\Definition\Proposal\DefinitionProposalInterface::propose()
     */
    public function propose(StepNode $step)
    {
        $text = $step->getText();

        $regex = preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+])/', '\\\\$1', $text);
        $regex = preg_replace(
            array(
                '/\'([^\']*)\'/', '/\"([^\"]*)\"/', // Quoted strings
                '/(\d+)/',                          // Numbers
            ),
            array(
                "\'([^\']*)\'", "\"([^\"]*)\"",
                "(\\d+)",
            ),
            $regex
        );
        $regex = preg_replace('/\'.*(?<!\')/', '\\\\$0', $regex); // Single quotes without matching pair (escape in resulting regex)
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
          , '%s', $regex, implode(', ', $args)
        );
        $type  = in_array($step->getType(), array('Given', 'When', 'Then')) ? $step->getType() : 'Given';

        return array(
            md5($description) => sprintf($description, str_replace(' ', '_', $type))
        );
    }
}

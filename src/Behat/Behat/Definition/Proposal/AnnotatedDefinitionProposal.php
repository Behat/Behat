<?php

namespace Behat\Behat\Definition\Proposal;

use Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Definition\DefinitionSnippet,
    Behat\Behat\Util\Transliterator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Annotated definitions proposal.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class AnnotatedDefinitionProposal implements DefinitionProposalInterface
{
    private static $proposedMethods = array();

    /**
     * Checks if loader supports provided context.
     *
     * @param ContextInterface $context
     *
     * @return Boolean
     */
    public function supports(ContextInterface $context)
    {
        return true;
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
        $contextRefl     = new \ReflectionObject($context);
        $contextClass    = $contextRefl->getName();
        $replacePatterns = array(
            "/(?<= |^)\\\'(?:((?!\\').)*)\\\'(?= |$)/", // Single quoted strings
            '/(?<= |^)\"(?:[^\"]*)\"(?= |$)/',          // Double quoted strings
            '/(\d+)/',                                  // Numbers
        );

        $text  = $step->getText();
        $text  = preg_replace('/([\/\[\]\(\)\\\^\$\.\|\?\*\+\'])/', '\\\\$1', $text);
        $regex = preg_replace(
            $replacePatterns,
            array(
                "\\'([^\']*)\\'",
                "\"([^\"]*)\"",
                "(\\d+)",
            ),
            $text
        );

        preg_match('/' . $regex . '/', $step->getText(), $matches);
        $count = count($matches) - 1;

        $methodName = preg_replace($replacePatterns, '', $text);
        $methodName = Transliterator::transliterate($methodName, ' ');
        $methodName = preg_replace('/[^a-zA-Z\_\ ]/', '', $methodName);
        $methodName = str_replace(' ', '', ucwords($methodName));

        if (0 !== strlen($methodName)) {
            $methodName[0] = strtolower($methodName[0]);
        } else {
            $methodName = 'stepDefinition1';
        }

        // get method number from method name
        $methodNumber = 2;
        if (preg_match('/(\d+)$/', $methodName, $matches)) {
            $methodNumber = intval($matches[1]);
        }

        // check that proposed method name isn't arelady defined in context
        while ($contextRefl->hasMethod($methodName)) {
            $methodName  = preg_replace('/\d+$/', '', $methodName);
            $methodName .= $methodNumber++;
        }

        // check that proposed method name haven't been proposed earlier
        if (isset(self::$proposedMethods[$contextClass])) {
            foreach (self::$proposedMethods[$contextClass] as $proposedRegex => $proposedMethod) {
                if ($proposedRegex !== $regex) {
                    while ($proposedMethod === $methodName) {
                        $methodName  = preg_replace('/\d+$/', '', $methodName);
                        $methodName .= $methodNumber++;
                    }
                }
            }
        }
        self::$proposedMethods[$contextClass][$regex] = $methodName;

        $args = array();
        for ($i = 0; $i < $count; $i++) {
            $args[] = "\$arg" . ($i + 1);
        }

        foreach ($step->getArguments() as $argument) {
            if ($argument instanceof PyStringNode) {
                $args[] = "PyStringNode \$string";
            } elseif ($argument instanceof TableNode) {
                $args[] = "TableNode \$table";
            }
        }

        $description = $this->generateSnippet($regex, $methodName, $args);

        return new DefinitionSnippet($step, $description);
    }

    protected function generateSnippet($regex, $methodName, array $args)
    {
        return sprintf(<<<PHP
    /**
     * @%s /^%s$/
     */
    public function %s(%s)
    {
        throw new PendingException();
    }
PHP
          , '%s', str_replace('%', '%%', $regex), $methodName, implode(', ', $args)
        );
    }
}

<?php

namespace Behat\Behat\Definition;

use Symfony\Component\Translation\TranslatorInterface;

use Behat\Gherkin\Node\StepNode,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Definition\Proposal\DefinitionProposalDispatcher,
    Behat\Behat\Exception\RedundantException,
    Behat\Behat\Exception\AmbiguousException,
    Behat\Behat\Exception\UndefinedException,
    Behat\Behat\Context\ContextInterface,
    Behat\Behat\Definition\DefinitionSnippet;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Definition dispatcher.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class DefinitionDispatcher
{
    private $transformations = array();
    private $definitions     = array();
    private $proposalDispatcher;
    private $translator;

    /**
     * Initializes definition dispatcher.
     *
     * @param DefinitionProposalDispatcher $proposalDispatcher
     * @param TranslatorInterface          $translator
     */
    public function __construct(DefinitionProposalDispatcher $proposalDispatcher, TranslatorInterface $translator)
    {
        $this->proposalDispatcher   = $proposalDispatcher;
        $this->translator           = $translator;
    }

    /**
     * Adds definition to dispatcher.
     *
     * @param DefinitionInterface $definition
     *
     * @throws RedundantException
     */
    public function addDefinition(DefinitionInterface $definition)
    {
        $regex = $definition->getRegex();

        if (isset($this->definitions[$regex])) {
            throw new RedundantException($definition, $this->definitions[$regex]);
        }

        $this->definitions[$regex] = $definition;
    }

    /**
     * Returns array of available definitions.
     *
     * @return array array of hashes => array(regex => definition)
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Adds transformation to dispatcher.
     *
     * @param TransformationInterface $transformation
     */
    public function addTransformation(TransformationInterface $transformation)
    {
        $this->transformations[] = $transformation;
    }

    /**
     * Returns array of available transformations.
     *
     * @return array array of argument transformers
     */
    public function getTransformations()
    {
        return $this->transformations;
    }

    /**
     * Cleans dispatcher.
     */
    public function clean()
    {
        $this->definitions     = array();
        $this->transformations = array();
    }

    /**
     * Finds step definition, that match specified step.
     *
     * @param ContextInterface $context
     * @param StepNode         $step
     * @param bool             $skip
     *
     * @return Definition
     *
     * @uses loadDefinitions()
     *
     * @throws AmbiguousException if step description is ambiguous
     * @throws UndefinedException if step definition not found
     */
    public function findDefinition(ContextInterface $context, StepNode $step, $skip = false)
    {
        $text       = $step->getText();
        $multiline  = $step->getArguments();
        $matches    = array();

        // find step to match
        foreach ($this->getDefinitions() as $origRegex => $definition) {
            $transRegex = $this->translateDefinitionRegex($origRegex, $step->getLanguage());

            // if not regex really (string) - transform into it
            if (0 !== strpos($origRegex, '/')) {
                $origRegex  = '/^'.preg_quote($origRegex, '/').'$/';
                $transRegex = '/^'.preg_quote($transRegex, '/').'$/';
            }

            if (preg_match($origRegex, $text, $arguments)
            || ($origRegex !== $transRegex && preg_match($transRegex, $text, $arguments))) {
                // prepare callback arguments
                $arguments = $this->prepareCallbackArguments(
                    $context, $definition->getCallbackReflection(), array_slice($arguments, 1), $multiline
                );

                if (!$skip) {
                    // transform arguments
                    foreach ($arguments as &$argument) {
                        foreach ($this->getTransformations() as $trans) {
                            $transRegex = $this->translateDefinitionRegex(
                                $trans->getRegex(), $step->getLanguage()
                            );

                            $newArgument = $trans->transform($transRegex, $context, $argument);
                            if (null !== $newArgument) {
                                $argument = $newArgument;
                            }
                        }
                    }
                }

                // set matched definition
                $definition->setMatchedText($text);
                $definition->setValues($arguments);
                $matches[] = $definition;
            }
        }

        if (count($matches) > 1) {
            throw new AmbiguousException($text, $matches);
        }

        if (0 === count($matches)) {
            throw new UndefinedException($text);
        }

        return $matches[0];
    }

    /**
     * Returns step definition for step node.
     *
     * @param ContextInterface $context
     * @param StepNode         $step
     *
     * @return DefinitionSnippet
     */
    public function proposeDefinition(ContextInterface $context, StepNode $step)
    {
        return $this->proposalDispatcher->propose($context, $step);
    }

    /**
     * Translates definition regex to provided language (if possible).
     *
     * @param string $regex    regex to translate
     * @param string $language language
     *
     * @return string
     */
    public function translateDefinitionRegex($regex, $language)
    {
        return $this->translator->trans($regex, array(), 'behat.definitions', $language);
    }

    /**
     * Merges found arguments with multiliners and maps them to the function callback signature.
     *
     * @param ContextInterface            $context   context instance
     * @param \ReflectionFunctionAbstract $refl      callback reflection
     * @param array                       $arguments found arguments
     * @param array                       $multiline multiline arguments of the step
     *
     * @return array
     */
    private function prepareCallbackArguments(ContextInterface $context, \ReflectionFunctionAbstract $refl,
                                              array $arguments, array $multiline)
    {
        $parametersRefl = $refl->getParameters();

        if ($refl->isClosure()) {
            array_shift($parametersRefl);
        }

        $resulting = array();
        foreach ($parametersRefl as $num => $parameterRefl) {
            if (isset($arguments[$parameterRefl->getName()])) {
                $resulting[] = $arguments[$parameterRefl->getName()];
            } elseif (isset($arguments[$num])) {
                $resulting[] = $arguments[$num];
            }
        }

        foreach ($multiline as $argument) {
            $resulting[] = $argument;
        }

        return $resulting;
    }
}

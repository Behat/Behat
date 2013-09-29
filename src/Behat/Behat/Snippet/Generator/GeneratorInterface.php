<?php

namespace Behat\Behat\Snippet\Generator;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Snippet\SnippetInterface;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\StepNode;

/**
 * Snippet generator interface.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * Checks if generator supports suite, contextPool and step.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contextPool
     * @param StepNode             $step
     *
     * @return Boolean
     */
    public function supports(SuiteInterface $suite, ContextPoolInterface $contextPool, StepNode $step);

    /**
     * Generates snippet.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contextPool
     * @param StepNode             $step
     *
     * @return SnippetInterface
     */
    public function generate(SuiteInterface $suite, ContextPoolInterface $contextPool, StepNode $step);
}

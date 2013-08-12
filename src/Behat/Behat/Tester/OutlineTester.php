<?php

namespace Behat\Behat\Tester;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Behat\Behat\Context\Pool\ContextPoolInterface;
use Behat\Behat\Event\EventInterface;
use Behat\Behat\Event\OutlineEvent;
use Behat\Behat\EventDispatcher\DispatchingService;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Behat\Tester\Event\OutlineExampleTesterCarrierEvent;
use Behat\Gherkin\Node\OutlineNode;
use RuntimeException;

/**
 * Outline tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester extends DispatchingService
{
    /**
     * Tests outline.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     *
     * @return integer
     */
    public function test(SuiteInterface $suite, ContextPoolInterface $contexts, OutlineNode $outline)
    {
        $event = new OutlineEvent($suite, $contexts, $outline);
        $this->dispatch(EventInterface::BEFORE_OUTLINE, $event);

        $result = 0;
        foreach ($outline->getExamples()->getHash() as $iteration => $tokens) {
            $tester = $this->getOutlineExampleTester($suite, $contexts, $outline, $iteration, $tokens);
            $result = max($result, $tester->test($suite, $contexts, $outline, $iteration, $tokens));
        }

        $event = new OutlineEvent($suite, $contexts, $outline, $result);
        $this->dispatch(EventInterface::AFTER_OUTLINE, $event);

        return $result;
    }

    /**
     * Returns outline example tester.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param integer              $iteration
     * @param array                $tokens
     *
     * @return OutlineExampleTester
     *
     * @throws RuntimeException If outline example tester is not found
     */
    private function getOutlineExampleTester(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $iteration,
        array $tokens
    )
    {
        $testerProvider = new Event\OutlineExampleTesterCarrierEvent($suite, $contexts, $outline, $iteration, $tokens);

        $this->dispatch(EventInterface::CREATE_OUTLINE_EXAMPLE_TESTER, $testerProvider);
        if (!$testerProvider->hasTester()) {
            throw new RuntimeException(sprintf(
                'Can not find tester for example #%d of "%s" outline from the "%s" suite.',
                $iteration,
                $outline->getTitle(),
                $suite->getName()
            ));
        }

        return $testerProvider->getTester();
    }
}

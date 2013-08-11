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
use Behat\Behat\Event\OutlineExampleEvent;
use Behat\Behat\Suite\SuiteInterface;
use Behat\Gherkin\Node\OutlineNode;

/**
 * Outline example tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineExampleTester extends IsolatedStepCollectionTester
{
    /**
     * Tests outline example.
     *
     * @param SuiteInterface       $suite
     * @param ContextPoolInterface $contexts
     * @param OutlineNode          $outline
     * @param integer              $iteration
     * @param array                $tokens
     *
     * @return integer
     */
    public function test(
        SuiteInterface $suite,
        ContextPoolInterface $contexts,
        OutlineNode $outline,
        $iteration,
        array $tokens
    )
    {
        $contexts = $this->initializeContextPool($suite, $contexts);

        $event = new OutlineExampleEvent($suite, $contexts, $outline, $iteration);
        $this->dispatch(EventInterface::BEFORE_OUTLINE_EXAMPLE, $event);

        $result = 0;
        if ($outline->getFeature()->hasBackground()) {
            $background = $outline->getFeature()->getBackground();

            $tester = $this->getBackgroundTester($suite, $contexts, $background);
            $result = $tester->test($suite, $outline, $background, $contexts);
        }

        foreach ($outline->getSteps() as $step) {
            $step = $step->createExampleRowStep($tokens);

            $tester = $this->getStepTester($suite, $contexts, $step, $result);
            $result = max($result, $tester->test($suite, $contexts, $step, $outline));
        }

        $event = new OutlineExampleEvent($suite, $contexts, $outline, $iteration, $result);
        $this->dispatch(EventInterface::AFTER_OUTLINE_EXAMPLE, $event);

        return $result;
    }
}

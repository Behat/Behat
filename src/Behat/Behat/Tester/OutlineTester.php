<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode,
    Behat\Gherkin\Node\OutlineNode;

use Behat\Behat\Event\OutlineEvent,
    Behat\Behat\Event\OutlineExampleEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Outline tester.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class OutlineTester extends ScenarioTester
{
    /**
     * Initializes tester.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
    }

    /**
     * Visits & tests OutlineNode.
     *
     * @param AbstractNode $outline
     *
     * @return integer
     */
    public function visit(AbstractNode $outline)
    {
        $this->dispatcher->dispatch('beforeOutline', new OutlineEvent($outline));

        $result = 0;

        // Run examples of outline
        foreach ($outline->getExamples()->getHash() as $iteration => $tokens) {
            $itResult = $this->visitOutlineExample($outline, $iteration, $tokens);

            $result = max($result, $itResult);
        }

        $this->dispatcher->dispatch('afterOutline', new OutlineEvent($outline, $result));

        return $result;
    }

    /**
     * Visits & tests OutlineNode example.
     *
     * @param OutlineNode $outline outline instance
     * @param integer     $row     row number
     * @param array       $tokens  step replacements for tokens
     *
     * @return integer
     */
    private function visitOutlineExample(OutlineNode $outline, $row, array $tokens = array())
    {
        $context  = $this->container->get('behat.context.dispatcher')->createContext();
        $itResult = 0;
        $skip     = false;

        $this->dispatcher->dispatch('beforeOutlineExample', new OutlineExampleEvent(
            $outline, $row, $context
        ));

        // Visit & test background if has one
        if ($outline->getFeature()->hasBackground()) {
            $bgResult = $this->visitBackground(
                $outline->getFeature()->getBackground(), $outline, $context
            );
            if (0 !== $bgResult) {
                $skip = true;
            }
            $itResult = max($itResult, $bgResult);
        }

        // Visit & test steps
        foreach ($outline->getSteps() as $step) {
            $stResult = $this->visitStep($step, $outline, $context, $tokens, $skip);
            if (0 !== $stResult) {
                $skip = true;
            }
            $itResult = max($itResult, $stResult);
        }

        $this->dispatcher->dispatch('afterOutlineExample', new OutlineExampleEvent(
            $outline, $row, $context, $itResult, $skip
        ));

        return $itResult;
    }
}

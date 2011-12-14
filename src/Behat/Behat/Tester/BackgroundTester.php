<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode;

use Behat\Behat\Context\ContextInterface,
    Behat\Behat\Event\BackgroundEvent;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Background tester.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class BackgroundTester implements NodeVisitorInterface
{
    /**
     * Service container.
     *
     * @var     Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    /**
     * Event dispatcher.
     *
     * @var     Behat\Behat\EventDispatcher\EventDispatcher
     */
    private $dispatcher;
    /**
     * Context.
     *
     * @var     Behat\Behat\Context\ContextInterface
     */
    private $context;
    /**
     * Dry run of background.
     *
     * @var     Boolean
     */
    private $dryRun = false;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
        $this->dispatcher = $container->get('behat.event_dispatcher');
    }

    /**
     * Sets run context.
     *
     * @param   Behat\Behat\Context\ContextInterface    $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * Sets tester to dry-run mode.
     *
     * @param   Boolean $dryRun
     */
    public function setDryRun($dryRun = true)
    {
        $this->dryRun = (bool) $dryRun;
    }

    /**
     * Visits & tests BackgroundNode.
     *
     * @param   Behat\Gherkin\Node\AbstractNode $background
     *
     * @return  integer
     */
    public function visit(AbstractNode $background)
    {
        $this->dispatcher->dispatch('beforeBackground', new BackgroundEvent($background));

        $result = 0;
        $skip   = false;

        // Visit & test steps
        foreach ($background->getSteps() as $step) {
            $tester = $this->container->get('behat.tester.step');
            $tester->setContext($this->context);
            $tester->skip($skip || $this->dryRun);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->dispatch('afterBackground', new BackgroundEvent($background, $result, $skip));

        return $result;
    }
}

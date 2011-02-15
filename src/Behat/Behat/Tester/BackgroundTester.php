<?php

namespace Behat\Behat\Tester;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\EventDispatcher\Event;

use Behat\Gherkin\Node\NodeVisitorInterface,
    Behat\Gherkin\Node\AbstractNode;

use Behat\Behat\Environment\EnvironmentInterface;

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
    protected $container;
    /**
     * Event dispatcher.
     *
     * @var     Behat\Behat\EventDispatcher\EventDispatcher
     */
    protected $dispatcher;
    /**
     * Environment.
     *
     * @var     Behat\Behat\Environment\EnvironmentInterface
     */
    protected $environment;

    /**
     * Initializes tester.
     *
     * @param   Symfony\Component\DependencyInjection\ContainerInterface    $container  service container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dispatcher   = $container->get('behat.event_dispatcher');
    }

    /**
     * Sets run environment.
     *
     * @param   Behat\Behat\Environment\EnvironmentInterface    $environment
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
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
        $this->dispatcher->notify(new Event($background, 'background.before'));

        $result = 0;
        $skip   = false;

        // Visit & test steps
        foreach ($background->getSteps() as $step) {
            $tester = $this->container->get('behat.tester.step');
            $tester->setEnvironment($this->environment);
            $tester->skip($skip);

            $stResult = $step->accept($tester);

            if (0 !== $stResult) {
                $skip = true;
            }
            $result = max($result, $stResult);
        }

        $this->dispatcher->notify(new Event($background, 'background.after', array(
            'result'    => $result,
            'skipped'   => $skip
        )));

        return $result;
    }
}

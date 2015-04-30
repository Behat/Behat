<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Tester\Runtime;

use Behat\Behat\Definition\Call\DefinitionCall;
use Behat\Behat\Definition\DefinitionFinder;
use Behat\Behat\Definition\Exception\SearchException;
use Behat\Behat\Definition\SearchResult;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Behat\Tester\Result\FailedStepSearchResult;
use Behat\Behat\Tester\Result\SkippedStepResult;
use Behat\Behat\Tester\Result\StepResult;
use Behat\Behat\Tester\Result\UndefinedStepResult;
use Behat\Behat\Tester\StepTester;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Testwork\Call\CallCenter;
use Behat\Testwork\Call\CallResult;
use Behat\Testwork\Environment\Environment;
use Behat\Testwork\Tester\Setup\SuccessfulSetup;
use Behat\Testwork\Tester\Setup\SuccessfulTeardown;

/**
 * Tester executing step tests in the runtime.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
final class RuntimeStepTester implements StepTester
{
    /** Number of seconds to attempt "Then" steps before accepting a failure */
    const TIMEOUT = 5;

    /**
     * @var DefinitionFinder
     */
    private $definitionFinder;
    /**
     * @var CallCenter
     */
    private $callCenter;

    /** @var string The last "Given", "When", or "Then" keyword encountered */
    protected $lastKeyword;

    /**
     * Initialize tester.
     *
     * @param DefinitionFinder $definitionFinder
     * @param CallCenter       $callCenter
     */
    public function __construct(DefinitionFinder $definitionFinder, CallCenter $callCenter)
    {
        $this->definitionFinder = $definitionFinder;
        $this->callCenter = $callCenter;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(Environment $env, FeatureNode $feature, StepNode $step, $skip)
    {
        return new SuccessfulSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function test(Environment $env, FeatureNode $feature, StepNode $step, $skip = false)
    {
        try {
            $search = $this->searchDefinition($env, $feature, $step);
            $result = $this->testDefinition($env, $feature, $step, $search, $skip);
        } catch (SearchException $exception) {
            $result = new FailedStepSearchResult($exception);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(Environment $env, FeatureNode $feature, StepNode $step, $skip, StepResult $result)
    {
        return new SuccessfulTeardown();
    }

    /**
     * Searches for a definition.
     *
     * @param Environment $env
     * @param FeatureNode $feature
     * @param StepNode    $step
     *
     * @return SearchResult
     */
    private function searchDefinition(Environment $env, FeatureNode $feature, StepNode $step)
    {
        return $this->definitionFinder->findDefinition($env, $feature, $step);
    }

    /**
     * Tests found definition.
     *
     * @param Environment  $env
     * @param FeatureNode  $feature
     * @param StepNode     $step
     * @param SearchResult $search
     * @param Boolean      $skip
     *
     * @return StepResult
     */
    private function testDefinition(Environment $env, FeatureNode $feature, StepNode $step, SearchResult $search, $skip)
    {
        $keyword = $step->getKeyword();
        if (in_array($keyword, ['Given', 'When', 'Then'])) {
          // We've entered a new major keyword block. Record the keyword.
          // This allows us to know where we are when processing And or But steps
          $this->lastKeyword = $keyword;
        }

        if (!$search->hasMatch()) {
            return new UndefinedStepResult();
        }

        if ($skip) {
            return new SkippedStepResult($search);
        }

        $call = $this->createDefinitionCall($env, $feature, $search, $step);

        $lambda = function() use ($call) {
          return $this->callCenter->makeCall($call);
        };

        // @todo We can only "spin" if we are interacting with a remote browser. If the browser is
        // running in the same thread as this test (such as with Goutte or Zombie), then spinning
        // will only prevent that process from continuing, and the test will either pass immediately,
        // or not at all. We need to find out how to check what Driver we're using...

        // if we're in a Then (assertion) block, we need to spin
        $result = $this->lastKeyword == 'Then' ? $this->spin($lambda) : $lambda();

        return new ExecutedStepResult($search, $result);
    }

    /**
     * Continually calls an assertion until it passes or the timeout is reached.
     *
     * @param  callable   $lambda The lambda assertion to call. Must take no arguments and return
     *                            a CallResult.
     * @return CallResult
     */
    protected function spin(callable $lambda)
    {
      $lastError = null;

      $start = microtime(true);

      while (microtime(true) - $start < self::TIMEOUT) {
        /** @var $result CallResult */
        $result = $lambda();

        if (!$result->hasException() || ($result->getException() instanceof PendingException)) {
          return $result;
        }
      }

      return $result;
    }

    /**
     * Creates definition call.
     *
     * @param Environment  $env
     * @param FeatureNode  $feature
     * @param SearchResult $search
     * @param StepNode     $step
     *
     * @return DefinitionCall
     */
    private function createDefinitionCall(Environment $env, FeatureNode $feature, SearchResult $search, StepNode $step)
    {
        $definition = $search->getMatchedDefinition();
        $arguments = $search->getMatchedArguments();

        return new DefinitionCall($env, $feature, $step, $definition, $arguments);
    }
}

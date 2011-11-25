Feature: HTML Formatter
  In order to print features
  As a feature writer
  I need to have an html formatter

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\ClosuredContextInterface,
          Behat\Behat\Context\BehatContext,
          Behat\Behat\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;
      use Symfony\Component\Finder\Finder;

      if (file_exists(__DIR__ . '/../support/bootstrap.php')) {
          require_once __DIR__ . '/../support/bootstrap.php';
      }

      class FeatureContext extends BehatContext implements ClosuredContextInterface
      {
          public $parameters = array();

          public function __construct(array $parameters) {
              $this->parameters = $parameters;

              if (file_exists(__DIR__ . '/../support/env.php')) {
                  $world = $this;
                  require(__DIR__ . '/../support/env.php');
              }
          }

          public function getStepDefinitionResources() {
              if (file_exists(__DIR__ . '/../steps')) {
                  $finder = new Finder();
                  return $finder->files()->name('*.php')->in(__DIR__ . '/../steps');
              }
              return array();
          }

          public function getHookDefinitionResources() {
              if (file_exists(__DIR__ . '/../support/hooks.php')) {
                  return array(__DIR__ . '/../support/hooks.php');
              }
              return array();
          }

          public function __call($name, array $args) {
              if (isset($this->$name) && is_callable($this->$name)) {
                  return call_user_func_array($this->$name, $args);
              } else {
                  $trace = debug_backtrace();
                  trigger_error(
                      'Call to undefined method ' . get_class($this) . '::' . $name .
                      ' in ' . $trace[0]['file'] .
                      ' on line ' . $trace[0]['line'],
                      E_USER_ERROR
                  );
              }
          }
      }
      """
    And a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """
    And a file named "features/steps/math.php" with:
      """
      <?php
      $steps->Given('/I have entered (\d+)/', function($world, $num) {
          assertObjectNotHasAttribute('value', $world);
          $world->value = $num;
      });

      $steps->Then('/I must have (\d+)/', function($world, $num) {
          assertEquals($num, $world->value);
      });

      $steps->When('/I (add|subtract) the value (\d+)/', function($world, $op, $num) {
          if ($op == 'add')
            $world->value += $num;
          elseif ($op == 'subtract')
            $world->value -= $num;
      });
      """

  Scenario: Multiple parameters
    Given a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat -f html"
    Then it should pass
    And the output should contain:
      """
      <div class="feature">
      <h2>
      <span class="keyword">Feature: </span>
      <span class="title">World consistency</span>
      </h2>
      <p>
      In order to maintain stable behaviors<br />
      As a features developer<br />
      I want, that &quot;World&quot; flushes between scenarios<br />
      </p>

      <div class="scenario background">
      <h3>
      <span class="keyword">Background: </span>
      <span class="path">features/World.feature:6</span>
      </h3>
      <ol>
      <li class="passed">
      <div class="step">
      <span class="keyword">Given </span>
      <span class="text">I have entered <strong class="passed_param">10</strong></span>
      <span class="path">features/steps/math.php:2</span>
      </div>
      </li>
      </ol>
      </div>
      <div class="scenario">
      <h3>
      <span class="keyword">Scenario: </span>
      <span class="title">Adding</span>
      <span class="path">features/World.feature:9</span>
      </h3>
      <ol>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">10</strong></span>
      <span class="path">features/steps/math.php:7</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">And </span>
      <span class="text">I <strong class="passed_param">add</strong> the value <strong class="passed_param">6</strong></span>
      <span class="path">features/steps/math.php:11</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">16</strong></span>
      <span class="path">features/steps/math.php:7</span>
      </div>
      </li>
      </ol>
      </div>
      <div class="scenario">
      <h3>
      <span class="keyword">Scenario: </span>
      <span class="title">Subtracting</span>
      <span class="path">features/World.feature:14</span>
      </h3>
      <ol>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">10</strong></span>
      <span class="path">features/steps/math.php:7</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">And </span>
      <span class="text">I <strong class="passed_param">subtract</strong> the value <strong class="passed_param">6</strong></span>
      <span class="path">features/steps/math.php:11</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">4</strong></span>
      <span class="path">features/steps/math.php:7</span>
      </div>
      </li>
      </ol>
      </div>
      </div>
      """

  Scenario: Scenario outline examples table
    Given a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario Outline: Adding
          Then I must have 10
          And I add the value <n>
          Then I must have <total>

          Examples:
            | n  | total |
            | 5  | 15    |
            | 10 | 21    |
      """
    When I run "behat -f html"
    Then the output should contain:
      """
      <div class="scenario outline">
      <h3>
      <span class="keyword">Scenario Outline: </span>
      <span class="title">Adding</span>
      <span class="path">features/World.feature:9</span>
      </h3>
      <ol>
      <li class="skipped">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="skipped_param">10</strong></span>
      <span class="path">features/steps/math.php:7</span>
      </div>
      </li>
      <li class="skipped">
      <div class="step">
      <span class="keyword">And </span>
      <span class="text">I add the value <strong class="skipped_param"><n></strong></span>
      <span class="path">features/steps/math.php:11</span>
      </div>
      </li>
      <li class="skipped">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="skipped_param"><total></strong></span>
      <span class="path">features/steps/math.php:7</span>
      </div>
      </li>
      </ol>
      <div class="examples">
      <h4>Examples</h4>
      <table>
      <thead>
      <tr class="skipped">
      <td>n</td>
      <td>total</td>
      </tr>
      </thead>
      <tbody>
      <tr class="passed">
      <td>5</td>
      <td>15</td>
      </tr>
      <tr class="failed">
      <td>10</td>
      <td>21</td>
      </tr>
      <tr class="failed exception">
      <td colspan="2">
      <pre class="backtrace">Failed asserting that 20 matches expected '21'.</pre>
      </td>
      </tr>
      </tbody>
      </table>
      </div>
      </div>
      """

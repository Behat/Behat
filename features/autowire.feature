Feature: Helper services autowire
  In order to speed up the development process at early stages
  developers need to have a convenient way of requesting services without going through the explicit configuration layer

  Rules:
    - Autowiring only works with helper containers
    - Autowiring is off by default
    - Autowiring is enabled/disabled by a suite-level `autowire` flag
    - It works for context constructor arguments
    - It works for step definition arguments
    - It works for transformation arguments
    - It only wires arguments that weren't otherwise set
    - Services must be last arguments in step definitions
    - Services must be last arguments in transformations
    - Autowiring is not yet triggered for union types

  Background:
    Given a file named "behat.yaml" with:
      """
      default:
        suites:
          default:
            autowire: true
            services: ServiceContainer
      """
    And a file named "features/bootstrap/ServiceContainer.php" with:
      """
      <?php use Psr\Container\ContainerInterface;

      class Service1 {public $state;}
      class Service2 {public $state; public $myFlag;}
      class Service3 {public $state;}
      class Service4 {public $state;}

      class ServiceContainer implements ContainerInterface {
          private $services = array();

          public function has($class): bool {
              return in_array($class, array('Service1', 'Service2', 'Service3'));
          }

          public function get($class) {
              if (!$this->has($class))
                  throw new \Behat\Behat\HelperContainer\Exception\ServiceNotFoundException("Service $class not found", $class);

              return isset($this->services[$class])
                   ? $this->services[$class]
                   : $this->services[$class] = new $class;
          }
      }
      """

  Scenario: Constructor arguments
    Given a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          Given a step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          public function __construct(Service2 $s1, Service1 $s2, Service3 $s3) {}

          /** @Given a step */
          public function aStep() {}
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should pass

  Scenario: Mixed constructor arguments
    Given a file named "behat.yaml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext:
                  name: "Konstantin"
                  s2: "@Service2"
            services: ServiceContainer
            autowire: true
      """
    And a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          Given a step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          public function __construct(Service2 $s2, $name, Service1 $s1, Service3 $s3)
          {
              PHPUnit\Framework\Assert::assertEquals('Konstantin', $name);
          }

          /** @Given a step */
          public function aStep() {}
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should pass

  Scenario: Null arguments should be skipped
    Given a file named "behat.yaml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FeatureContext:
                  name: null
            services: ServiceContainer
            autowire: true
      """
    And a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          Given a step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          public function __construct($name, Service1 $s1, Service3 $s3)
          {
              PHPUnit\Framework\Assert::assertNull($name);
          }

          /** @Given a step */
          public function aStep() {}
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should pass

  Scenario: Unregistered services as constructor arguments
    Given a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          Given a step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          public function __construct(Service4 $s) {}

          /** @Given a step */
          public function aStep() {}
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should fail with:
      """
      Service Service4 not found
      """

  Scenario: Step definition arguments
    Given a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          When I set the state to "isSet"
          Then that state should be persisted as "isSet"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          /** @When I set the state to :value */
          public function setState($value, Service2 $s2) {
              $s2->state = $value;
          }

          /** @Then that state should be persisted as :value */
          public function checkState($val, Service2 $s2) {
              PHPUnit\Framework\Assert::assertEquals($val, $s2->state);
          }
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should pass

  Scenario: Unregistered step definition argument
    Given a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          Given a step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          /** @Given a step */
          public function aStep(Service4 $s) {}
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should fail with:
      """
      Service Service4 not found
      """

  Scenario: Transformation arguments
    Given a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          When I set the "myFlag" flag to "isSet"
          Then the "myFlag" flag should be persisted as "isSet"
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          /** @Transform :flag */
          public function fromFlag($flag, Service2 $s2) {
              return $s2->$flag;
          }

          /** @When I set the :flat flag to :value */
          public function setState($flag, $value, Service2 $s2) {
              $s2->$flag = $value;
          }

          /** @Then the :flag flag should be persisted as :value */
          public function checkState($flag, $value) {
              PHPUnit\Framework\Assert::assertEquals($value, $flag);
          }
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should pass

  @php8
  Scenario: Union constructor arguments
    Given a file named "features/autowire.feature" with:
      """
      Feature:
        Scenario:
          Given a step
      """
    And a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FeatureContext implements Context {
          public function __construct(Service1|Service2 $s) {}

          /** @Given a step */
          public function aStep() {}
      }
      """
    When I run "behat --no-colors -f progress features/autowire.feature"
    Then it should fail with:
      """
      Can not find a matching value for an argument `$s` of the method `FeatureContext::__construct()`
      """

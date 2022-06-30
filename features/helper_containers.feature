Feature: Per-suite helper containers
  In order to share state and behaviour between contexts
  developers need to have a way to create or register shared service container

  Rules:
    - A single optional container is allowed per suite
    - Having a container enables you to use its services as context arguments via `@name` syntax
    - Container is rebuilt and is isolated between scenarios
    - Container is configured via suite's `services` option
    - Container is a class implementing `Psr\Container\ContainerInterface`
    - There is a built-in container if you need a very simple service-sharing, configurable through the same `services` setting
    - There is an extension point that allows Behat extensions provide their own containers for end-users via `@name` syntax

  Out of scope:
    - Extensive service configuration and deep dependency trees support for built-in container. Behat is not your DIC framework
    - Sharing scalar, non-object parameters using container. Use YAML anchors and references for configuration sharing
    - Multiple containers per suite. Would introduce unnecessary complexity. Also, easily achievable manually through composition
    - PSR-11 support. It was not accepted as a standard as of feature implementation. Support might be added later, subject to prioritisation

  Usage:
    - Use built-in container for simple dependency trees and when no DIC is used
    - Use external container (ideally the one used for application itself) for deep dependency trees
    - Use extension-provided containers when working with frameworks (if provided by framework)

  Background:
    Given a file named "features/container.feature" with:
      """
      Feature:
        Scenario:
          Given service has no state
          When service gets a state of 1 in first context
          Then service should have a state of 1 in second context

        Scenario:
          Given service has no state
          When service gets a state of 33 in first context
          Then service should have a state of 33 in second context
      """
    And a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FirstContext implements Context {
          public function __construct(SharedService $service) {
              $this->service = $service;
          }

          /** @Given service has no state */
          public function noState() {
              PHPUnit\Framework\Assert::assertNull($this->service->number);
          }

          /** @When service gets a state of :number in first context */
          public function setState($number) {
              $this->service->number = $number;
          }
      }
      """
    And a file named "features/bootstrap/SecondContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class SecondContext implements Context {
          public function __construct(SharedService $service) {
              $this->service = $service;
          }

          /** @Then service should have a state of :number in second context */
          public function checkState($number) {
              PHPUnit\Framework\Assert::assertSame($number, $this->service->number);
          }
      }
      """
    And a file named "features/bootstrap/SharedService.php" with:
      """
      <?php

      class SharedService {
          public $number = null;
      }
      """
    And a file named "features/bootstrap/SharedServiceExpecting1.php" with:
      """
      <?php

      class SharedServiceExpecting1 extends SharedService {
          public function __construct($arg) {
              if (1 !== $arg) {
                  throw new \InvalidArgumentException();
              }
          }
      }
      """
    And a file named "features/bootstrap/SharedServiceWithFactory.php" with:
      """
      <?php

      class SharedServiceWithFactory extends SharedService {
          private function __construct() {}

          public static function factoryMethod() {
              return new self();
          }
      }
      """

  Scenario: External container
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services: MyContainer
      """
    And a file named "features/bootstrap/MyContainer.php" with:
      """
      <?php use Psr\Container\ContainerInterface;

      class MyContainer implements ContainerInterface {
          private $service;

          public function has($id): bool {
              return $id == 'shared_service';
          }

          public function get($id) {
              if ($id !== 'shared_service') throw new \InvalidArgumentException();
              return isset($this->service) ? $this->service : $this->service = new SharedService();
          }
      }
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: External container with a factory method
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services: MyContainer::factoryMethod
      """
    And a file named "features/bootstrap/MyContainer.php" with:
      """
      <?php use Psr\Container\ContainerInterface;

      class MyContainer implements ContainerInterface {
          private $service;
          private function __construct() {}

          public static function factoryMethod() {
              return new self();
          }

          public function has($id): bool {
              return $id == 'shared_service';
          }

          public function get($id) {
              if ($id !== 'shared_service') throw new \InvalidArgumentException();
              return isset($this->service) ? $this->service : $this->service = new SharedService();
          }
      }
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: PSR container
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services: MyContainer
      """
    And a file named "features/bootstrap/MyContainer.php" with:
      """
      <?php use Psr\Container\ContainerInterface;

      class MyContainer implements ContainerInterface {
          private $service;

          public function has($id): bool {
              return $id == 'shared_service';
          }

          public function get($id) {
              if ($id !== 'shared_service') throw new \InvalidArgumentException();
              return isset($this->service) ? $this->service : $this->service = new SharedService();
          }
      }
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Simplest built-in container configuration
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services:
              shared_service: SharedService
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Built-in container with service arguments
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services:
              shared_service:
                class: SharedServiceExpecting1
                arguments:
                  - 1
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Built-in container with class names as service IDs
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@SharedService"
              - SecondContext:
                - "@SharedService"

            services:
              SharedService: ~
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Built-in container with class names as service IDs and arguments
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@SharedServiceExpecting1"
              - SecondContext:
                - "@SharedServiceExpecting1"

            services:
              SharedServiceExpecting1:
                arguments:
                  - 1
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Built-in container with factory-based services
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services:
              shared_service:
                class: SharedServiceWithFactory
                factory_method: factoryMethod
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Container provided by an extension
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@shared_service"
              - SecondContext:
                - "@shared_service"

            services: "@my_extension.container"

        extensions:
          MyExtension.php: ~
      """
    And a file named "features/bootstrap/MyContainer.php" with:
      """
      <?php use Psr\Container\ContainerInterface;

      class MyContainer implements ContainerInterface {
          private $service;

          public function has($id): bool {
              return $id == 'shared_service';
          }

          public function get($id) {
              if ($id !== 'shared_service') throw new \InvalidArgumentException();
              return isset($this->service) ? $this->service : $this->service = new SharedService();
          }
      }
      """
    And a file named "MyExtension.php" with:
      """
      <?php

      use Behat\Testwork\ServiceContainer\Extension;
      use Behat\Testwork\ServiceContainer\ExtensionManager;
      use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;
      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
      use Symfony\Component\DependencyInjection\Definition;
      use Symfony\Component\DependencyInjection\ContainerBuilder;

      class MyExtension implements Extension {
          public function getConfigKey() { return 'container_provider'; }
          public function configure(ArrayNodeDefinition $builder) { }
          public function initialize(ExtensionManager $extensionManager) {}
          public function process(ContainerBuilder $container) {}

          public function load(ContainerBuilder $container, array $config) {
              $definition = new Definition('MyContainer', array());
              $definition->addTag(HelperContainerExtension::HELPER_CONTAINER_TAG);
              $definition->setPublic(true);

              if (method_exists($definition, 'setShared')) {
                  $definition->setShared(false); // <- Starting Symfony 2.8
              } else {
                  $definition->setScope($container::SCOPE_PROTOTYPE); // <- Up to Symfony 2.8
              }

              $container->setDefinition('my_extension.container', $definition);
          }
      }

      return new MyExtension;
      """
    When I run "behat --no-colors -f progress features/container.feature"
    Then it should pass

  Scenario: Mix of typehinted arguments and numbered arguments (fix #991)
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@typehinted_service"
                - bar

            services:
              typehinted_service:
                class: stdClass
      """
    And a file named "features/container_args.feature" with:
      """
      Feature:
        Scenario:
          Given foo
      """
    And a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FirstContext implements Context {
          public function __construct(stdClass $service, $bar) {
          }

          /** @Given foo */
          public function foo() {
          }
      }
      """
    When I run "behat --no-colors -f progress features/container_args.feature"
    Then it should pass

  Scenario: Injecting typehinted arguments for a parent and child class (fix #1008)
    Given a file named "behat.yml" with:
      """
      default:
        suites:
          default:
            contexts:
              - FirstContext:
                - "@child_class"
                - "@parent_class"
            services:
              parent_class:
                class: ParentClass
              child_class:
                class: ChildClass
      """
    And a file named "features/container_args.feature" with:
      """
      Feature:
        Scenario:
          Given foo
      """
    And a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php use Behat\Behat\Context\Context;

      class FirstContext implements Context {
        public function __construct(ParentClass $parent, ChildClass $child) {
        }

        /** @Given foo */
        public function foo() {

        }
      }
      """
    And a file named "features/bootstrap/ParentClass.php" with:
      """
      <?php
      class ParentClass {}
      """
    And a file named "features/bootstrap/ChildClass.php" with:
      """
      <?php
      class ChildClass extends ParentClass {}
      """
    When I run "behat --no-colors -f progress features/container_args.feature"
    Then it should pass

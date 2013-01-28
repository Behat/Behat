@feature/injectableContext
Feature: Injectable Context
  In order to use a business abstraction layer
  As a feature writer
  I need to have contexts injectable by dependency injection

  Background:
    Given a file named "features/bootstrap/bootstrap.php" with:
    """
        <?php
        require_once 'PHPUnit/Autoload.php';
        require_once 'PHPUnit/Framework/Assert/Functions.php';
        """
    Given a file named "SimpleExtension.php" with:
    """
      <?php
      use Behat\Behat\Extension\ExtensionInterface;
      use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
          Symfony\Component\Config\FileLocator,
          Symfony\Component\DependencyInjection\ContainerBuilder,
          Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

      class SimpleExtension implements ExtensionInterface
      {
          public function load(array $config, ContainerBuilder $container)
          {
              $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
              $loader->load('my.custom.di.config.xml');
          }

          public function getConfig(ArrayNodeDefinition $builder) {}
          public function getCompilerPasses() { return array(); }
      }
      return new SimpleExtension();
      """
    Given a file named "behat.yml" with:
    """
    default:
      extensions:
        SimpleExtension.php: ~
    """
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
        <?php

        use Behat\Behat\Context\BehatContext, Behat\Behat\Exception\PendingException;
        use Behat\Gherkin\Node\PyStringNode,  Behat\Gherkin\Node\TableNode;

        class FeatureContext extends BehatContext
        {
            private $value;

            /**
             * @Given /I have entered (\d+)/
             */
            public function iHaveEntered($num) {
                $this->value = $num;
            }

            /**
             * @Then /I must have (\d+)/
             */
            public function iMustHave($num) {
                assertEquals($num, $this->value);
            }

            /**
             * @When /I (add|subtract) the value (\d+)/
             */
            public function iAddOrSubstact($op, $num) {
                if ($op == 'add')
                  $this->value += $num;
                elseif ($op == 'subtract')
                  $this->value -= $num;
            }
        }
        """

  Scenario: Defining prototype scopes flushes context
    Given a file named "my.custom.di.config.xml" with:
    """
    <?xml version="1.0" ?>
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
        <services>
            <service id="behat.context.dispatcher" alias="behat.context.dispatcher.injectable" />
            <service id="behat.context.injectable" class="%behat.context.class%" scope="prototype">
                <argument>%behat.context.parameters%</argument>
            </service>
        </services>
    </container>
    """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding some interesting
                  value
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting
                  some
                  value
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
        """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
        """
        Feature: World consistency
          In order to maintain stable behaviors
          As a features developer
          I want, that "World" flushes between scenarios

          Background:               # features/World.feature:6
            Given I have entered 10 # FeatureContext::iHaveEntered()

          Scenario: Adding some interesting # features/World.feature:9
                    value
            Then I must have 10             # FeatureContext::iMustHave()
            And I add the value 6           # FeatureContext::iAddOrSubstact()
            Then I must have 16             # FeatureContext::iMustHave()

          Scenario: Subtracting             # features/World.feature:15
                    some
                    value
            Then I must have 10             # FeatureContext::iMustHave()
            And I subtract the value 6      # FeatureContext::iAddOrSubstact()
            Then I must have 4              # FeatureContext::iMustHave()

        2 scenarios (2 passed)
        8 steps (8 passed)
        """

  Scenario: Defining no scope returns shared context object
    Given a file named "my.custom.di.config.xml" with:
    """
    <?xml version="1.0" ?>
    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
        <services>
            <service id="behat.context.dispatcher" alias="behat.context.dispatcher.injectable" />
            <service id="behat.context.injectable" class="%behat.context.class%">
                <argument>%behat.context.parameters%</argument>
            </service>
        </services>
    </container>
    """
    And a file named "features/World.feature" with:
    """
    Feature: World consistency
      In order to maintain stable behaviors
      As a features developer
      I want, that "World" flushes between scenarios

      Scenario: Adding some interesting
                value
        Given I have entered 10
        Then I must have 10
        And I add the value 6
        Then I must have 16

      Scenario: Subtracting
                some
                value
        Then I must have 16
        And I subtract the value 6
        Then I must have 10
      """
    When I run "behat --no-ansi -f pretty"
    Then it should pass with:
    """
    Feature: World consistency
      In order to maintain stable behaviors
      As a features developer
      I want, that "World" flushes between scenarios

      Scenario: Adding some interesting # features/World.feature:6
                value
        Given I have entered 10         # FeatureContext::iHaveEntered()
        Then I must have 10             # FeatureContext::iMustHave()
        And I add the value 6           # FeatureContext::iAddOrSubstact()
        Then I must have 16             # FeatureContext::iMustHave()

      Scenario: Subtracting             # features/World.feature:13
                some
                value
        Then I must have 16             # FeatureContext::iMustHave()
        And I subtract the value 6      # FeatureContext::iAddOrSubstact()
        Then I must have 10             # FeatureContext::iMustHave()

    2 scenarios (2 passed)
    7 steps (7 passed)
    """

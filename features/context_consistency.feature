Feature: Context Consistency
  In order to maintain stable behavior tests
  As a feature writer
  I need a separate context for every scenario/outline

  Background:
    Given I initialise the working directory from the "ContextConsistency" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: True "apples story"
    When I run "behat -f progress features/apples.feature"
    Then it should pass with:
      """
      ..................

      5 scenarios (5 passed)
      18 steps (18 passed)
      """

  Scenario: False "apples story"
    When I run "behat -f progress features/apples-false.feature"
    Then it should fail with:
      """
      ..F..F...F.......F

      --- Failed steps:

      001 Scenario: I'm little hungry   # features/apples-false.feature:9
            Then I should have 5 apples # features/apples-false.feature:11
              Failed asserting that 2 matches expected 5.

      002 Scenario: Found more apples    # features/apples-false.feature:13
            Then I should have 10 apples # features/apples-false.feature:15
              Failed asserting that 13 matches expected 10.

      003 Example: | 3   | 1     | 3      | # features/apples-false.feature:24
            Then I should have 3 apples     # features/apples-false.feature:20
              Failed asserting that 1 matches expected 3.

      004 Example: | 2   | 2     | 4      | # features/apples-false.feature:26
            Then I should have 4 apples     # features/apples-false.feature:20
              Failed asserting that 3 matches expected 4.

      5 scenarios (1 passed, 4 failed)
      18 steps (14 passed, 4 failed)
      """

  Scenario: Context parameters
    When I run "behat --profile params -f progress features/params.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Context parameters including optional
    When I run "behat --profile params_optional -f progress features/params-optional.feature"
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

  Scenario: Existing custom context class
    When I run "behat --profile custom_context -f progress --snippets-type=regex --snippets-for=CustomContext features/params.feature"
    Then it should pass with:
      """
      UU

      1 scenario (1 undefined)
      2 steps (2 undefined)

      --- CustomContext has missing steps. Define them with these snippets:

          #[Then('/^context parameter "([^"]*)" should be equal to "([^"]*)"$/')]
          public function contextParameterShouldBeEqualTo($arg1, $arg2): void
          {
              throw new PendingException();
          }

          #[Then('/^context parameter "([^"]*)" should be array with (\d+) elements$/')]
          public function contextParameterShouldBeArrayWithElements($arg1, $arg2): void
          {
              throw new PendingException();
          }

      --- Don't forget these 2 use statements:

          use Behat\Behat\Tester\Exception\PendingException;
          use Behat\Step\Then;
      """

  Scenario: Single context class instead of an array provided as `contexts` option
    When I run "behat --profile single_context -f progress features/params.feature"
    Then it should fail with:
      """
      `contexts` setting of the "default" suite is expected to be an array, string given.
      """

  Scenario: Unexisting custom context class
    When I run "behat --profile unexisting_context -f progress features/params.feature"
    Then it should fail with:
      """
      `UnexistentContext` context class not found and can not be used.
      """

  Scenario: Unexisting context argument
    When I run "behat --profile unexisting_param -f progress features/params.feature"
    Then it should fail with:
      """
      `CoreContext::__construct()` does not expect argument `$unexistingParam`.
      """

  Scenario: Suite without contexts and FeatureContext available
    When I run "behat --profile empty_contexts -fpretty --format-settings='{\"paths\": true}' features/some.feature"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # FeatureContext::iHaveApples()
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 2 apples # FeatureContext::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples

      2 scenarios (1 passed, 1 undefined)
      6 steps (3 passed, 3 undefined)

      --- Use --snippets-for CLI option to generate snippets for following first suite steps:

          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """

  Scenario: Suite with custom context and FeatureContext available
    When I run "behat --profile custom_context_with_hook -fpretty --format-settings='{\"paths\": true}' features/hook.feature"
    Then it should pass with:
      """
      Feature:

        Scenario:    # features/hook.feature:2
          Given step

        Scenario:    # features/hook.feature:4
          Given step

      Feature:

        ┌─ @BeforeScenario # CustomContextWithHook::beforeScenario()
        │
        │  Setting up
        │
        Scenario:    # features/hook.feature:2
          Given step # CustomContextWithHook::step()

        ┌─ @BeforeScenario # CustomContextWithHook::beforeScenario()
        │
        │  Setting up
        │
        Scenario:    # features/hook.feature:4
          Given step # CustomContextWithHook::step()

      4 scenarios (2 passed, 2 undefined)
      4 steps (2 passed, 2 undefined)

      --- Use --snippets-for CLI option to generate snippets for following default suite steps:

          Given step
      """

  Scenario: Array arguments
    When I run "behat --profile array_arguments -f progress features/context-args-array.feature"
    Then it should pass

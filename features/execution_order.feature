Feature: Setting order of execution
  As a scenario writer
  In order to detect dependencies between my scenarios
  I should be able to specify the order in which they are run

  Background:
    Given I initialise the working directory from the "ExecutionOrder" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: No order specified
    When I run "behat"
    Then it should pass with:
      """
      Feature: Feature 1

        Scenario:               # features/order1.feature:3
          Given I have 1 orange # FeatureContext::iHaveOranges()
          Then I have 1 orange  # FeatureContext::iHaveOranges()

      Feature: Feature 2

        Scenario:                # features/order2.feature:3
          Given I have 2 oranges # FeatureContext::iHaveOranges()
          Then I have 2 oranges  # FeatureContext::iHaveOranges()

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

    Scenario: Unknown order
      When I run "behat --order=foo"
      Then it should fail with:
      """
      Order option 'foo' was not recognised
      """

  Scenario: Reverse order
    When I run "behat --order=reverse"
    Then it should pass with:
      """
      Feature: Feature 2

        Scenario:                # features/order2.feature:3
          Given I have 2 oranges # FeatureContext::iHaveOranges()
          Then I have 2 oranges  # FeatureContext::iHaveOranges()

      Feature: Feature 1

        Scenario:               # features/order1.feature:3
          Given I have 1 orange # FeatureContext::iHaveOranges()
          Then I have 1 orange  # FeatureContext::iHaveOranges()

      2 scenarios (2 passed)
      4 steps (4 passed)
      """

  Scenario: Random order
      When I run "behat --order=random"
      Then it should pass

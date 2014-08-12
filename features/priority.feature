Feature: Prioritisation
  As a scenario writer
  In order to detect dependencies between my scenarios
  I should be able to specify the order in which they are run

  Background:
    Given a file named "features/bootstrap/FeatureContext.php" with:
    """
      <?php

      use Behat\Behat\Context\Context;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements Context
      {
          /**
           * @Given I have :num orange(s)
           */
           public function iHaveOranges($num){}
      }
      """
    And a file named "features/order1.feature" with:
    """
      Feature: Feature 1

        Scenario:
          Given I have 1 orange
          Then I have 1 orange
      """
    And a file named "features/order2.feature" with:
    """
      Feature: Feature 2

        Scenario:
          Given I have 2 oranges
          Then I have 2 oranges
      """

  Scenario: No priority specified
    When I run "behat -fpretty"
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

    Scenario: Unknown priority
      When I run "behat -fpretty --priority=foo"
      Then it should fail with:
      """
      [Behat\Behat\Tester\Exception\BadPriorityException]
        Priority option 'foo' was not recognised



      behat [-s|--suite="..."] [-f|--format="..."] [-o|--out="..."] [--format-settings="..."] [--init] [--lang="..."] [--name="..."] [--tags="..."] [--role="..."] [--story-syntax] [-d|--definitions="..."] [--append-snippets] [--no-snippets] [--strict] [--priority="..."] [--rerun] [--stop-on-failure] [--dry-run] [paths]
      """

  Scenario: Reverse priority
    When I run "behat -fpretty --priority=reverse"
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

  Scenario: Random priority
    When I run "behat -fpretty --priority=random"
    Then it should pass

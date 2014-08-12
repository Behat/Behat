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
    And a file named "features/order.feature" with:
    """
      Feature: Ordered feature

        Scenario:
          Given I have 1 orange
          Then I have 1 orange

        Scenario:
          Given I have 2 oranges
          Then I have 2 oranges

        Scenario:
          Given I have 3 oranges
          Then I have 3 oranges
      """

  Scenario: No priority specified
    When I run "behat -fpretty"
    Then it should pass with:
      """
      Feature: Ordered feature

        Scenario:               # features/order.feature:3
          Given I have 1 orange # FeatureContext::iHaveOranges()
          Then I have 1 orange  # FeatureContext::iHaveOranges()

        Scenario:                # features/order.feature:7
          Given I have 2 oranges # FeatureContext::iHaveOranges()
          Then I have 2 oranges  # FeatureContext::iHaveOranges()

        Scenario:                # features/order.feature:11
          Given I have 3 oranges # FeatureContext::iHaveOranges()
          Then I have 3 oranges  # FeatureContext::iHaveOranges()

      3 scenarios (3 passed)
      6 steps (6 passed)
      """

    Scenario: Unknown priority

      When I run "behat -fpretty --priority=foo"
      Then it should fail with:
      """
      [Behat\Behat\Tester\Exception\BadPriorityException]
        Priority option 'foo' was not recognised



      behat [-s|--suite="..."] [-f|--format="..."] [-o|--out="..."] [--format-settings="..."] [--init] [--lang="..."] [--name="..."] [--tags="..."] [--role="..."] [--story-syntax] [-d|--definitions="..."] [--append-snippets] [--no-snippets] [--strict] [--priority="..."] [--rerun] [--stop-on-failure] [--dry-run] [paths]
      """
Feature: Narrative filters
  In order to run only needed features
  As a Behat user
  I need Behat to support features filtering based on a regex filter on the narrative

  Background:
    Given I initialise the working directory from the "NarrativeFilters" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Brothers
    When I run "behat --narrative '/As a (little|big) kid/'"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/little_kid.feature:6
          Given I have 3 apples       # FeatureContext::iHaveApples()
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 2 apples # FeatureContext::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

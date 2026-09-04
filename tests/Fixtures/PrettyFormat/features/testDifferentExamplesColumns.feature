Feature: Behat can run scenarios whose examples tables do not share a structure

  Scenario Outline: Differently shaped examples
    When I input <name>
    Then I should see "<result>"

    Examples: columns in the order used by the steps
      | name | result |
      | Bob  | Hi Bob |

    Examples: the same columns in a different order
      | result | name |
      | Hi Bob | Bob  |

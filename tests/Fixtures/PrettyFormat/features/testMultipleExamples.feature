Feature: Behat can run scenarios with multiple examples tables
  In order to make the purpose of my examples clear
  As a feature writer
  I need to group examples into separate tables

  Scenario Outline: Grouped examples
    When I input <name>
    Then I should see "<result>"

    Examples: valid cases
      | name  | result   |
      | Bob   | Hi Bob   |
      | Jenny | Hi Jenny |

    Examples: invalid cases
      | name   | result                             |
      | 123456 | '123456' doesn't look like a name? |
      | Brian  | Sorry Brian, you're banned         |

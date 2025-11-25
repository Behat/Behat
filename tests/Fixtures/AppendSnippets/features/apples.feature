Feature: Apples story
  In order to eat apple
  As a little kid
  I need to have an apple in my pocket

  Background:
    Given I have 3 apples

  Scenario: I'm little hungry
    When I ate 1 apple
    Then I should have 3 apples

  Scenario: Found more apples
    When I found 5 apples
    Then I should have 8 apples

  Scenario: Found more apples
    When I found 2 apples
    Then I should have 5 apples
    And do something undefined with $

  Scenario Outline: Other situations
    When I ate <ate> apples
    And I found <found> apples
    Then I should have <result> apples
    And do something undefined with \1

    Examples:
      | ate | found | result |
      | 3   | 1     | 1      |
      | 0   | 4     | 8      |
      | 2   | 2     | 3      |

  Scenario: Multilines
    Given pystring:
      """
      some pystring
      """
    And pystring 5:
      """
      other pystring
      """
    And table:
      | col1 | col2 |
      | val1 | val2 |

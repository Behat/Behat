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

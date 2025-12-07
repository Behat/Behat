Feature: Failed coffee machine actions
  In order to know about coffee machine failures
  As a coffee buyer
  I need to be able to know about failed actions

  Background:
    Given I have thrown 10$ into machine

  Scenario: Check thrown amount
    Then I should see 12$ on the screen

  Scenario: Additional throws
    Given I have thrown 20$ into machine
    Then I should see 31$ on the screen
    And I should see 33$ on the screen

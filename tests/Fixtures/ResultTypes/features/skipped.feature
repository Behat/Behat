Feature: Skipped coffee machine actions
  In order to tell clients about failures faster
  As a coffee machine
  I need to be able to skip unneeded steps

  Background:
    Given human bought coffee

  Scenario: I have no water
    Given I have no water
    And I have electricity
    When I boil water
    Then the coffee should be almost done

  Scenario: I have no electricity
    Given I have water
    And I have no electricity
    When I boil water
    Then the coffee should be almost done

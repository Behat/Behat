Feature: Pending coffee machine actions
  In order to make some long making drinks
  As a coffee machine
  I need to be able to make pending actions

  Background:
    Given human have ordered very very very hot "coffee"

  Scenario: When the coffee ready
    When the coffee will be ready
    Then I should say "Take your cup!"

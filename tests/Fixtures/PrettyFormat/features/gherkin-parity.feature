@gherkin-parity
Feature: Parity with basic feature file
  In order to see that the pretty formatter can handle different gherkin modes
    As a developer
I need to see an example with basic Gherkin syntax

  Background: That sets up the calculator
      to have a starting number
     for every scenario

    Given I have entered 25


  Scenario: Simple passing scenario
      describing what happens when
       numbers are correctly added.
    When  I add 2
    Then  I must have 27

  Scenario: Simple failing scenario
    When  I add 3
    Then  I must have 30

  @wip @new
  Scenario: With unknown step
    When  I subtract 15
    Then  I must have 10

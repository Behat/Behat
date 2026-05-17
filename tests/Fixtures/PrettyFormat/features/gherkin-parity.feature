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

  @with-examples
  Scenario Outline: Cases that dynamically check the calculator
      using inputs from the sets of tables

      When I add <input>
      Then   I must have <expect>

      @simple
      Examples: First set
        with a description
        | input | expect |
        | 3     | 28     |
        | 4     | 29     |

      @big
      Examples: Some more examples
         that cover bigger numbers
         | input | expect |
         | 180   | 205    |

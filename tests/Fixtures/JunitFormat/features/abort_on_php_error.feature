Feature: Abort on PHP error
  In order to see correct information
  As a features developer
  I want to see the right output in JUnit when I abort due to a PHP error

  Background:
    Given I have entered 10

  Scenario: Failed
    When I have a PHP error
    Then I must have 14

Feature: Stop on failure
  In order to see test information
  As a features developer
  I want to see the right output in JUnit when I stop on a failure

  Background:
    Given I have entered 10

  Scenario: Failed
    When I add 4
    Then I must have 13

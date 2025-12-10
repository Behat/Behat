@slow
Feature: Feature N1

  Background:
    Given Some slow step N11

  Scenario:
    Given Some slow step N12
    And Some normal step N13

  @fast
  Scenario:
    Given Some fast step N14

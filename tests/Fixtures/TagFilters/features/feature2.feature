Feature: Feature N2

  Background:
    Given Some normal step N21

  @slow @fast
  Scenario:
    Given Some slow step N22
    And Some fast step N23

  @fast
  Scenario:
    Given Some fast step N24
    And Some fast step N25

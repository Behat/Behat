Feature: Patterns capturing more arguments than the definition accepts

  Scenario: A capturing group that the definition does not accept
    Given Alice presses the red button
    And Bob presses the green button

  Scenario: The same pattern using a non-capturing group
    Given Alice pushes the red button
    And Bob pushes the green button

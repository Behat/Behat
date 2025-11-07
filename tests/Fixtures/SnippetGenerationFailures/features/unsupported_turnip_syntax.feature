Feature: Steps that cannot be represented in turnip syntax

  Scenario:
    Given a step with "(parentheses)" inside a quoted parameter
    # Words in parentheses within a turnip pattern are optional - there is no official way to escape parentheses that
    # are actually part of the step text. This step would need to be reworded, or defined with a regex step.
    And   a step with (Parentheses) in the actual step text

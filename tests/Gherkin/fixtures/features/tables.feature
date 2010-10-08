Feature: A scenario outline
  # COMMENT
  Scenario Outline:
    Given I add <a> and <b>
    # comment
    When I pass a table argument
      | foo | bar |
      | bar | baz |
          #comment
    Then I the result should be <c>
#comment
    Examples:
      | a   | b   | c |
      | 1   | 2   | 3 |
      | 2   | 3   | 4 |

Feature: Doc strings
  Scenario:
    Given a doc string:
      """
      hello,
        w
         o
      r
      l
         d
      """
    Then the doc string must be equals to string 1

  Scenario: A step with no declared types still receives the raw Gherkin node
    Given an untyped step that takes "hello" and this text:
      """
      hello,
        w
         o
      r
      l
         d
      """
    Then the argument must be a raw PyStringNode
    And the other argument must be "hello"

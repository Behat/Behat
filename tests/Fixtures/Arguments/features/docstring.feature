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

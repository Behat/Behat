Feature: PyStrings
  Scenario:
    Given a pystring:
      """
      hello,
        w
         o
    r
     l
         d
      """
    Then it must be equals to string 1

Feature: PyStrings
  Scenario Outline:
    Given a pystring:
      """
      <word1>
        w
         o
    r
     <word2>
         d
      """
    Then it must be equals to string 1

    Examples:
      | word1  | word2 |
      | hello, | l     |

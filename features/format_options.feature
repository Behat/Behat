Feature: Format options
  In order to optimize behat output
  As a tester
  I need to be able to set options on behat runner

  Background:
    Given I initialise the working directory from the "FormatOptions" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: --no-colors option
    When I run "behat --snippets-for=FeatureContext --snippets-type=regex"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3. (Exception)

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed step: Then I should have 8 apples
              Failed asserting that 7 matches expected 8. (Exception)
            | 2   | 2     | 3      |

        Scenario: Multilines # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      --- Failed scenarios:

          features/apples.feature:9 (on line 11)
          features/apples.feature:30 (on line 25)

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('/^do something undefined$/')]
          public function doSomethingUndefined(): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      """

  Scenario: --no-paths option
    When I run "behat --no-colors --format-settings='{\"paths\": false}' --snippets-for=FeatureContext --snippets-type=regex"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:
          Given I have 3 apples

        Scenario: I'm little hungry
          When I ate 1 apple
          Then I should have 3 apples
            Failed asserting that 2 matches expected 3. (Exception)

        Scenario: Found more apples
          When I found 5 apples
          Then I should have 8 apples

        Scenario: Found more apples
          When I found 2 apples
          Then I should have 5 apples
          And do something undefined

        Scenario Outline: Other situations
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed step: Then I should have 8 apples
              Failed asserting that 7 matches expected 8. (Exception)
            | 2   | 2     | 3      |

        Scenario: Multilines
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      --- Failed scenarios:

          features/apples.feature:9 (on line 11)
          features/apples.feature:30 (on line 25)

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('/^do something undefined$/')]
          public function doSomethingUndefined(): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      """

  Scenario: --no-snippets option
    When I run "behat --no-colors --no-snippets"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3. (Exception)

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed step: Then I should have 8 apples
              Failed asserting that 7 matches expected 8. (Exception)
            | 2   | 2     | 3      |

        Scenario: Multilines # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      --- Failed scenarios:

          features/apples.feature:9 (on line 11)
          features/apples.feature:30 (on line 25)

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)
      """

  Scenario: --expand option
    When I run "behat --no-colors --format-settings='{\"expand\": true}' --snippets-for=FeatureContext --snippets-type=regex"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3. (Exception)

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples
          And I found <found> apples
          Then I should have <result> apples

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |      # features/apples.feature:29
              When I ate 3 apples         # FeatureContext::iAteApples()
              And I found 1 apples        # FeatureContext::iFoundApples()
              Then I should have 1 apples # FeatureContext::iShouldHaveApples()
            | 0   | 4     | 8      |      # features/apples.feature:30
              When I ate 0 apples         # FeatureContext::iAteApples()
              And I found 4 apples        # FeatureContext::iFoundApples()
              Then I should have 8 apples # FeatureContext::iShouldHaveApples()
                Failed asserting that 7 matches expected 8. (Exception)
            | 2   | 2     | 3      |      # features/apples.feature:31
              When I ate 2 apples         # FeatureContext::iAteApples()
              And I found 2 apples        # FeatureContext::iFoundApples()
              Then I should have 3 apples # FeatureContext::iShouldHaveApples()

        Scenario: Multilines # features/apples.feature:33
          Given pystring:
            '''
            some pystring
            '''
          And table:
            | col1 | col2 |
            | val1 | val2 |

      --- Failed scenarios:

          features/apples.feature:9 (on line 11)
          features/apples.feature:30 (on line 25)

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('/^do something undefined$/')]
          public function doSomethingUndefined(): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      """

  Scenario: --no-multiline option
    When I run "behat --format-settings='{\"multiline\": false}' --snippets-for=FeatureContext --snippets-type=regex"
    Then it should fail with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Background:             # features/apples.feature:6
          Given I have 3 apples # FeatureContext::iHaveApples()

        Scenario: I'm little hungry   # features/apples.feature:9
          When I ate 1 apple          # FeatureContext::iAteApples()
          Then I should have 3 apples # FeatureContext::iShouldHaveApples()
            Failed asserting that 2 matches expected 3. (Exception)

        Scenario: Found more apples   # features/apples.feature:13
          When I found 5 apples       # FeatureContext::iFoundApples()
          Then I should have 8 apples # FeatureContext::iShouldHaveApples()

        Scenario: Found more apples   # features/apples.feature:17
          When I found 2 apples       # FeatureContext::iFoundApples()
          Then I should have 5 apples # FeatureContext::iShouldHaveApples()
          And do something undefined

        Scenario Outline: Other situations   # features/apples.feature:22
          When I ate <ate> apples            # FeatureContext::iAteApples()
          And I found <found> apples         # FeatureContext::iFoundApples()
          Then I should have <result> apples # FeatureContext::iShouldHaveApples()

          Examples:
            | ate | found | result |
            | 3   | 1     | 1      |
            | 0   | 4     | 8      |
              Failed step: Then I should have 8 apples
              Failed asserting that 7 matches expected 8. (Exception)
            | 2   | 2     | 3      |

        Scenario: Multilines # features/apples.feature:33
          Given pystring:
            ...
          And table:
            ...

      --- Failed scenarios:

          features/apples.feature:9 (on line 11)
          features/apples.feature:30 (on line 25)

      7 scenarios (3 passed, 2 failed, 2 undefined)
      25 steps (20 passed, 2 failed, 3 undefined)

      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('/^do something undefined$/')]
          public function doSomethingUndefined(): void
          {
              throw new PendingException();
          }

          #[Given('/^pystring:$/')]
          public function pystring(PyStringNode $string): void
          {
              throw new PendingException();
          }

          #[Given('/^table:$/')]
          public function table(TableNode $table): void
          {
              throw new PendingException();
          }
      """

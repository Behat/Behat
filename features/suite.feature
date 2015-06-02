Feature: Suites
  In order to use specific set of contexts against specific set of features in single run
  As a feature tester
  I need to be able to use suites

  Scenario: One feature, two contexts
    Given a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php

      class FirstContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/bootstrap/SecondContext.php" with:
      """
      <?php

      class SecondContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          first:
            contexts: [ FirstContext ]
          second:
            contexts: [ SecondContext ]
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # FirstContext::iHaveApples()
          When I ate 1 apple          # FirstContext::iAteApples()
          Then I should have 2 apples # FirstContext::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/some.feature:6
          Given I have 3 apples       # SecondContext::iHaveApples()
          When I ate 1 apple          # SecondContext::iAteApples()
          Then I should have 2 apples # SecondContext::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Two contexts, two features
    Given a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php

      class FirstContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/bootstrap/SecondContext.php" with:
      """
      <?php

      class SecondContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/first/my.feature" with:
      """
      Feature: Apples story #1
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    And a file named "features/second/my.feature" with:
      """
      Feature: Apples story #2
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 30 apples
          When I ate 10 apple
          Then I should have 20 apples
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          first:
            paths:    [ %paths.base%/features/first ]
            contexts: [ FirstContext ]
          second:
            paths:    [ %paths.base%/features/second ]
            contexts: [ SecondContext ]
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Apples story #1
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/first/my.feature:6
          Given I have 3 apples       # FirstContext::iHaveApples()
          When I ate 1 apple          # FirstContext::iAteApples()
          Then I should have 2 apples # FirstContext::iShouldHaveApples()

      Feature: Apples story #2
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry    # features/second/my.feature:6
          Given I have 30 apples       # SecondContext::iHaveApples()
          When I ate 10 apple          # SecondContext::iAteApples()
          Then I should have 20 apples # SecondContext::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Suite with `paths` set to string instead of an array
    Given a file named "features/bootstrap/FirstContext.php" with:
      """
      <?php

      class FirstContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/bootstrap/SecondContext.php" with:
      """
      <?php

      class SecondContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/first/my.feature" with:
      """
      Feature: Apples story #1
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    And a file named "features/second/my.feature" with:
      """
      Feature: Apples story #2
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 30 apples
          When I ate 10 apple
          Then I should have 20 apples
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          first:
            paths:    %paths.base%/features/first
            contexts: [ FirstContext ]
          second:
            paths:    [ %paths.base%/features/second ]
            contexts: [ SecondContext ]
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should fail with:
      """
      Behat\Testwork\Suite\Exception\SuiteConfigurationException]
        `paths` setting of the "first" suite is expected to be an array, string given.
      """

  Scenario: Role-based suites
    Given a file named "features/bootstrap/LittleKidContext.php" with:
      """
      <?php

      class LittleKidContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/bootstrap/BigBrotherContext.php" with:
      """
      <?php

      class BigBrotherContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/little_kid.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    And a file named "features/big_brother.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 15 apples
          When I ate 10 apple
          Then I should have 5 apples
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          little_kid:
            contexts: [ LittleKidContext ]
            filters:
              role:   little kid
          big_brother:
            contexts: [ BigBrotherContext ]
            filters:
              role:   big brother
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/little_kid.feature:6
          Given I have 3 apples       # LittleKidContext::iHaveApples()
          When I ate 1 apple          # LittleKidContext::iAteApples()
          Then I should have 2 apples # LittleKidContext::iShouldHaveApples()

      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/big_brother.feature:6
          Given I have 15 apples      # BigBrotherContext::iHaveApples()
          When I ate 10 apple         # BigBrotherContext::iAteApples()
          Then I should have 5 apples # BigBrotherContext::iShouldHaveApples()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Running single suite
    Given a file named "features/bootstrap/LittleKidContext.php" with:
      """
      <?php

      class LittleKidContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/bootstrap/BigBrotherContext.php" with:
      """
      <?php

      class BigBrotherContext implements \Behat\Behat\Context\Context
      {
          /** @Given I have :count apple(s) */
          public function iHaveApples($count) { }

          /** @When I ate :count apple(s) */
          public function iAteApples($count) { }

          /** @Then I should have :count apple(s) */
          public function iShouldHaveApples($count) { }
      }
      """
    And a file named "features/little_kid.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a little kid
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 3 apples
          When I ate 1 apple
          Then I should have 2 apples
      """
    And a file named "features/big_brother.feature" with:
      """
      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry
          Given I have 15 apples
          When I ate 10 apple
          Then I should have 5 apples
      """
    And a file named "behat.yml" with:
      """
      default:
        suites:
          little_kid:
            contexts: [ LittleKidContext ]
            filters:
              role:   little kid
          big_brother:
            contexts: [ BigBrotherContext ]
            filters:
              role:   big brother
      """
    When I run "behat --no-colors -sbig_brother -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Apples story
        In order to eat apple
        As a big brother
        I need to have an apple in my pocket

        Scenario: I'm little hungry   # features/big_brother.feature:6
          Given I have 15 apples      # BigBrotherContext::iHaveApples()
          When I ate 10 apple         # BigBrotherContext::iAteApples()
          Then I should have 5 apples # BigBrotherContext::iShouldHaveApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

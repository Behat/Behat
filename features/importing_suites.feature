Feature: Importing suites
  In order to add more suites
  As a feature writer
  I need an ability to import external suite configuration files

  Background:
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
    And a file named "config/suites/first.php" with:
      """
      <?php

      $config = new Behat\Config\Config([
        'default' => [
          'suites' => [
            'first' => [
              'contexts' => [ 'FirstContext' ],
            ],
          ],
        ],
      ]);

      return $config;

      """
    And a file named "config/suites/second.php" with:
      """
      <?php

      $config = new Behat\Config\Config([
        'default' => [
          'suites' => [
            'second' => [
              'contexts' => [ 'SecondContext' ],
            ],
          ],
        ],
      ]);

      return $config;

      """

  Scenario: Importing one suite
    Given a file named "behat.php" with:
      """
      <?php

      $config = new Behat\Config\Config();
      $config->import('config/suites/first.php');

      return $config;

      """
    When I run "behat --suite=first --no-colors -fpretty --format-settings='{\"paths\": true}' features"
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

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Importing two suites, running one
    Given a file named "behat.php" with:
      """
      <?php

      $config = new Behat\Config\Config();
      $config
        ->import('config/suites/first.php')
        ->import('config/suites/second.php')
      ;

      return $config;

      """
    When I run "behat --suite=first --no-colors -fpretty --format-settings='{\"paths\": true}' features"
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

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Importing two suites, running all
    Given a file named "behat.php" with:
      """
      <?php

      $config = new Behat\Config\Config();
      $config->import(['config/suites/first.php', 'config/suites/second.php']);

      return $config;

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

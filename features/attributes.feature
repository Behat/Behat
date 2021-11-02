Feature: attributes
  In order to keep annotations shorter and faster to parse
  As a tester
  I need to be able to use PHP8 Attributes

  @php8
  Scenario: PHP 8 Step Attributes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Step\Given, Behat\Step\When, Behat\Step\Then;

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          #[Given('I have :count apple(s)')]
          #[Given('I have :count banana(s)')]
          public function iHaveFruit($count) { }

          #[When('I eat :count apple(s)')]
          #[When('I eat :count banana(s)')]
          public function iEatFruit($count) { }

          #[Then('I should have :count apple(s)')]
          #[Then('I should have :count banana(s)')]
          public function iShouldHaveFruit($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples
          Given I have 3 apples
          When I eat 1 apple
          Then I should have 2 apples

        Scenario: I'm little hungry for bananas
          Given I have 3 bananas
          When I eat 1 banana
          Then I should have 2 bananas
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # FeatureContext::iHaveFruit()
          When I eat 1 apple                   # FeatureContext::iEatFruit()
          Then I should have 2 apples          # FeatureContext::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # FeatureContext::iHaveFruit()
          When I eat 1 banana                   # FeatureContext::iEatFruit()
          Then I should have 2 bananas          # FeatureContext::iShouldHaveFruit()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  @php8
  Scenario: PHP 8 Hook Feature Hook Attributes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Step\Given, Behat\Step\When, Behat\Step\Then;
      use Behat\Hook\BeforeFeature, Behat\Hook\AfterFeature;

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          #[BeforeFeature]
          public static function beforeFeature()
          {
              echo '= BEFORE FEATURE =';
          }

          #[AfterFeature]
          public static function afterFeature()
          {
              echo '= AFTER FEATURE =';
          }

          #[BeforeFeature('Fruit story')]
          public static function beforeFruitStory()
          {
              echo '= BEFORE FRUIT STORY =';
          }

          #[AfterFeature('Fruit story')]
          public static function afterFruitStory()
          {
              echo '= AFTER FRUIT STORY =';
          }

          #[Given('I have :count apple(s)')]
          #[Given('I have :count banana(s)')]
          public function iHaveFruit($count) { }

          #[When('I eat :count apple(s)')]
          #[When('I eat :count banana(s)')]
          public function iEatFruit($count) { }

          #[Then('I should have :count apple(s)')]
          #[Then('I should have :count banana(s)')]
          public function iShouldHaveFruit($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples
          Given I have 3 apples
          When I eat 1 apple
          Then I should have 2 apples

        Scenario: I'm little hungry for bananas
          Given I have 3 bananas
          When I eat 1 banana
          Then I should have 2 bananas
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContext::beforeFeature()
      │
      │  = BEFORE FEATURE =
      │
      ┌─ @BeforeFeature Fruit story # FeatureContext::beforeFruitStory()
      │
      │  = BEFORE FRUIT STORY =
      │
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # FeatureContext::iHaveFruit()
          When I eat 1 apple                   # FeatureContext::iEatFruit()
          Then I should have 2 apples          # FeatureContext::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # FeatureContext::iHaveFruit()
          When I eat 1 banana                   # FeatureContext::iEatFruit()
          Then I should have 2 bananas          # FeatureContext::iShouldHaveFruit()

      │
      │  = AFTER FEATURE =
      │
      └─ @AfterFeature # FeatureContext::afterFeature()

      │
      │  = AFTER FRUIT STORY =
      │
      └─ @AfterFeature Fruit story # FeatureContext::afterFruitStory()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  @php8
  Scenario: PHP 8 Hook Scenario Hook Attributes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Step\Given, Behat\Step\When, Behat\Step\Then;
      use Behat\Hook\BeforeScenario, Behat\Hook\AfterScenario;

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          #[BeforeScenario]
          public function beforeScenario()
          {
              echo '= BEFORE SCENARIO =';
          }

          #[BeforeScenario('@bananas')]
          public function beforeBananas()
          {
              echo '= BEFORE BANANAS =';
          }

          #[AfterScenario]
          public function afterScenario()
          {
              echo '= AFTER SCENARIO =';
          }

          #[AfterScenario('@bananas')]
          public function afterBananas()
          {
              echo '= AFTER BANANAS =';
          }

          #[Given('I have :count apple(s)')]
          #[Given('I have :count banana(s)')]
          public function iHaveFruit($count) { }

          #[When('I eat :count apple(s)')]
          #[When('I eat :count banana(s)')]
          public function iEatFruit($count) { }

          #[Then('I should have :count apple(s)')]
          #[Then('I should have :count banana(s)')]
          public function iShouldHaveFruit($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples
          Given I have 3 apples
          When I eat 1 apple
          Then I should have 2 apples

        @bananas
        Scenario: I'm little hungry for bananas
          Given I have 3 bananas
          When I eat 1 banana
          Then I should have 2 bananas
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        ┌─ @BeforeScenario # FeatureContext::beforeScenario()
        │
        │  = BEFORE SCENARIO =
        │
        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # FeatureContext::iHaveFruit()
          When I eat 1 apple                   # FeatureContext::iEatFruit()
          Then I should have 2 apples          # FeatureContext::iShouldHaveFruit()
        │
        │  = AFTER SCENARIO =
        │
        └─ @AfterScenario # FeatureContext::afterScenario()

        ┌─ @BeforeScenario # FeatureContext::beforeScenario()
        │
        │  = BEFORE SCENARIO =
        │
        ┌─ @BeforeScenario @bananas # FeatureContext::beforeBananas()
        │
        │  = BEFORE BANANAS =
        │
        @bananas
        Scenario: I'm little hungry for bananas # features/some.feature:12
          Given I have 3 bananas                # FeatureContext::iHaveFruit()
          When I eat 1 banana                   # FeatureContext::iEatFruit()
          Then I should have 2 bananas          # FeatureContext::iShouldHaveFruit()
        │
        │  = AFTER SCENARIO =
        │
        └─ @AfterScenario # FeatureContext::afterScenario()
        │
        │  = AFTER BANANAS =
        │
        └─ @AfterScenario @bananas # FeatureContext::afterBananas()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  @php8
  Scenario: PHP 8 Hook Step Hook Attributes
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Step\Given, Behat\Step\When, Behat\Step\Then;
      use Behat\Hook\BeforeStep, Behat\Hook\AfterStep;

      class FeatureContext implements \Behat\Behat\Context\Context
      {
          #[BeforeStep]
          public function beforeStep()
          {
              echo '= BEFORE STEP =';
          }

          #[AfterStep]
          public function afterStep()
          {
              echo '= AFTER STEP =';
          }

          #[BeforeStep('I have 3 apples')]
          public function beforeApples()
          {
              echo '= BEFORE APPLES =';
          }

          #[AfterStep('I have 3 apples')]
          public function afterApples()
          {
              echo '= AFTER APPLES =';
          }

          #[Given('I have :count apple(s)')]
          #[Given('I have :count banana(s)')]
          public function iHaveFruit($count) { }

          #[When('I eat :count apple(s)')]
          #[When('I eat :count banana(s)')]
          public function iEatFruit($count) { }

          #[Then('I should have :count apple(s)')]
          #[Then('I should have :count banana(s)')]
          public function iShouldHaveFruit($count) { }
      }
      """
    And a file named "features/some.feature" with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples
          Given I have 3 apples
          When I eat 1 apple
          Then I should have 2 apples

        Scenario: I'm little hungry for bananas
          Given I have 3 bananas
          When I eat 1 banana
          Then I should have 2 bananas
      """
    When I run "behat --no-colors -fpretty --format-settings='{\"paths\": true}' features"
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          ┌─ @BeforeStep # FeatureContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          ┌─ @BeforeStep I have 3 apples # FeatureContext::beforeApples()
          │
          │  = BEFORE APPLES =
          │
          Given I have 3 apples                # FeatureContext::iHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # FeatureContext::afterStep()
          │
          │  = AFTER APPLES =
          │
          └─ @AfterStep I have 3 apples # FeatureContext::afterApples()
          ┌─ @BeforeStep # FeatureContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          When I eat 1 apple                   # FeatureContext::iEatFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # FeatureContext::afterStep()
          ┌─ @BeforeStep # FeatureContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Then I should have 2 apples          # FeatureContext::iShouldHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # FeatureContext::afterStep()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          ┌─ @BeforeStep # FeatureContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Given I have 3 bananas                # FeatureContext::iHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # FeatureContext::afterStep()
          ┌─ @BeforeStep # FeatureContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          When I eat 1 banana                   # FeatureContext::iEatFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # FeatureContext::afterStep()
          ┌─ @BeforeStep # FeatureContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Then I should have 2 bananas          # FeatureContext::iShouldHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # FeatureContext::afterStep()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

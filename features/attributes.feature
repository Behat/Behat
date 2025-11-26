Feature: attributes
  In order to keep annotations shorter and faster to parse
  As a tester
  I need to be able to use PHP Attributes

  Background:
    Given I initialise the working directory from the "Attributes" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |

  Scenario: Step Attributes
    When I run behat with the following additional options:
      | option                | value           |
      | --profile             | step_attributes |
      | features/some.feature |                 |
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # StepAttributesContext::iHaveFruit()
          When I eat 1 apple                   # StepAttributesContext::iEatFruit()
          Then I should have 2 apples          # StepAttributesContext::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # StepAttributesContext::iHaveFruit()
          When I eat 1 banana                   # StepAttributesContext::iEatFruit()
          Then I should have 2 bananas          # StepAttributesContext::iShouldHaveFruit()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Hook Feature Hook Attributes
    When I run behat with the following additional options:
      | option                | value         |
      | --profile             | feature_hooks |
      | features/some.feature |               |
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureHookAttributesContext::beforeFeature()
      │
      │  = BEFORE FEATURE =
      │
      ┌─ @BeforeFeature Fruit story # FeatureHookAttributesContext::beforeFruitStory()
      │
      │  = BEFORE FRUIT STORY =
      │
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          Given I have 3 apples                # FeatureHookAttributesContext::iHaveFruit()
          When I eat 1 apple                   # FeatureHookAttributesContext::iEatFruit()
          Then I should have 2 apples          # FeatureHookAttributesContext::iShouldHaveFruit()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          Given I have 3 bananas                # FeatureHookAttributesContext::iHaveFruit()
          When I eat 1 banana                   # FeatureHookAttributesContext::iEatFruit()
          Then I should have 2 bananas          # FeatureHookAttributesContext::iShouldHaveFruit()

      │
      │  = AFTER FEATURE =
      │
      └─ @AfterFeature # FeatureHookAttributesContext::afterFeature()

      │
      │  = AFTER FRUIT STORY =
      │
      └─ @AfterFeature Fruit story # FeatureHookAttributesContext::afterFruitStory()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Hook Scenario Hook Attributes
    When I run behat with the following additional options:
      | option                          | value          |
      | --profile                       | scenario_hooks |
      | features/some-with-tags.feature |                |
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        ┌─ @BeforeScenario # ScenarioHookAttributesContext::beforeScenario()
        │
        │  = BEFORE SCENARIO =
        │
        Scenario: I'm little hungry for apples # features/some-with-tags.feature:6
          Given I have 3 apples                # ScenarioHookAttributesContext::iHaveFruit()
          When I eat 1 apple                   # ScenarioHookAttributesContext::iEatFruit()
          Then I should have 2 apples          # ScenarioHookAttributesContext::iShouldHaveFruit()
        │
        │  = AFTER SCENARIO =
        │
        └─ @AfterScenario # ScenarioHookAttributesContext::afterScenario()

        ┌─ @BeforeScenario # ScenarioHookAttributesContext::beforeScenario()
        │
        │  = BEFORE SCENARIO =
        │
        ┌─ @BeforeScenario @bananas # ScenarioHookAttributesContext::beforeBananas()
        │
        │  = BEFORE BANANAS =
        │
        @bananas
        Scenario: I'm little hungry for bananas # features/some-with-tags.feature:12
          Given I have 3 bananas                # ScenarioHookAttributesContext::iHaveFruit()
          When I eat 1 banana                   # ScenarioHookAttributesContext::iEatFruit()
          Then I should have 2 bananas          # ScenarioHookAttributesContext::iShouldHaveFruit()
        │
        │  = AFTER SCENARIO =
        │
        └─ @AfterScenario # ScenarioHookAttributesContext::afterScenario()
        │
        │  = AFTER BANANAS =
        │
        └─ @AfterScenario @bananas # ScenarioHookAttributesContext::afterBananas()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

  Scenario: Hook Suite Hook Attributes
    When I run behat with the following additional options:
      | option                  | value       |
      | --profile               | suite_hooks |
      | features/apples.feature |             |
    Then it should pass with:
      """
      ┌─ @BeforeSuite # SuiteHookAttributesContext::beforeSuite()
      │
      │  = BEFORE SUITE =
      │

      ┌─ @BeforeSuite apples # SuiteHookAttributesContext::beforeSuiteApples()
      │
      │  = BEFORE APPLES =
      │

      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/apples.feature:6
          Given I have 3 apples                # SuiteHookAttributesContext::iHaveFruit()
          When I eat 1 apple                   # SuiteHookAttributesContext::iEatFruit()
          Then I should have 2 apples          # SuiteHookAttributesContext::iShouldHaveFruit()

      │
      │  = AFTER SUITE =
      │
      └─ @AfterSuite # SuiteHookAttributesContext::afterSuite()

      │
      │  = AFTER APPLES =
      │
      └─ @AfterSuite apples # SuiteHookAttributesContext::afterSuiteApples()

      1 scenario (1 passed)
      3 steps (3 passed)
      """

  Scenario: Hook Step Hook Attributes
    When I run behat with the following additional options:
      | option                | value      |
      | --profile             | step_hooks |
      | features/some.feature |            |
    Then it should pass with:
      """
      Feature: Fruit story
        In order to eat fruit
        As a little kid
        I need to have fruit in my pocket

        Scenario: I'm little hungry for apples # features/some.feature:6
          ┌─ @BeforeStep # StepHookAttributesContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          ┌─ @BeforeStep I have 3 apples # StepHookAttributesContext::beforeApples()
          │
          │  = BEFORE APPLES =
          │
          Given I have 3 apples                # StepHookAttributesContext::iHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # StepHookAttributesContext::afterStep()
          │
          │  = AFTER APPLES =
          │
          └─ @AfterStep I have 3 apples # StepHookAttributesContext::afterApples()
          ┌─ @BeforeStep # StepHookAttributesContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          When I eat 1 apple                   # StepHookAttributesContext::iEatFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # StepHookAttributesContext::afterStep()
          ┌─ @BeforeStep # StepHookAttributesContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Then I should have 2 apples          # StepHookAttributesContext::iShouldHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # StepHookAttributesContext::afterStep()

        Scenario: I'm little hungry for bananas # features/some.feature:11
          ┌─ @BeforeStep # StepHookAttributesContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Given I have 3 bananas                # StepHookAttributesContext::iHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # StepHookAttributesContext::afterStep()
          ┌─ @BeforeStep # StepHookAttributesContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          When I eat 1 banana                   # StepHookAttributesContext::iEatFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # StepHookAttributesContext::afterStep()
          ┌─ @BeforeStep # StepHookAttributesContext::beforeStep()
          │
          │  = BEFORE STEP =
          │
          Then I should have 2 bananas          # StepHookAttributesContext::iShouldHaveFruit()
          │
          │  = AFTER STEP =
          │
          └─ @AfterStep # StepHookAttributesContext::afterStep()

      2 scenarios (2 passed)
      6 steps (6 passed)
      """

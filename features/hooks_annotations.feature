Feature: Hooks Annotations
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks using annotations

  Background:
    Given I initialise the working directory from the "Hooks" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value                 |
      | --no-colors |                       |
      | --config    | behat-annotations.php |

  Scenario: Basic hooks execution
    When I run behat with the following additional options:
      | option                | value |
      | features/test.feature |       |
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContextAnnotations::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:             # features/test.feature:2
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 50 # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                 # features/test.feature:4
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Given I have entered 12 # FeatureContextAnnotations::iHaveEntered()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 12     # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @thirty
        Scenario:                # features/test.feature:9
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Given I must have 30   # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          When I have entered 23 # FeatureContextAnnotations::iHaveEntered()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 23    # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @100 @thirty
        Scenario:               # features/test.feature:14
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Given I must have 30  # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          When I have entered 1 # FeatureContextAnnotations::iHaveEntered()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 100  # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @filtered
        Scenario:                                                # features/test.feature:20
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have a scenario filter value of "filtered" # FeatureContextAnnotations::iMustHaveScenarioFilter()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @~filtered
        Scenario:                                                 # features/test.feature:24
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have a scenario filter value of "~filtered" # FeatureContextAnnotations::iMustHaveScenarioFilter()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContextAnnotations::doSomethingAfterFeature()
      
      6 scenarios (6 passed)
      11 steps (11 passed)
      """

  Scenario: Hooks on tagged features
    When I run behat with the following additional options:
      | option                | value |
      | features/some.feature |       |
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContextAnnotations::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      ┌─ @BeforeFeature @someFeature # FeatureContextAnnotations::doSomethingBeforeSomeFeature()
      │
      │  = do something before SOME feature
      │
      @someFeature
      Feature:
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:             # features/some.feature:3
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 50 # FeatureContextAnnotations::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContextAnnotations::doSomethingAfterFeature()
      
      │
      │  = do something after SOME feature
      │
      └─ @AfterFeature @someFeature # FeatureContextAnnotations::doSomethingAfterSomeFeature()
      
      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Hooks output
    When I run behat with the following additional options:
      | option                        | value |
      | features/hooks-output.feature |       |
    Then it should fail with:
      """
      ┌─ @BeforeFeature # FeatureContextAnnotations::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/hooks-output.feature:2
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Given passing step # FeatureContextAnnotations::passingStep()
            │ Is passing
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/hooks-output.feature:5
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          Given failing step # FeatureContextAnnotations::failingStep()
            Failing (RuntimeException)
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAnnotations::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                              # features/hooks-output.feature:8
          ┌─ @BeforeStep # FeatureContextAnnotations::passingBeforeStep()
          │
          │  Is passing
          │
          ┌─ @BeforeStep passing step with failing hook # FeatureContextAnnotations::failingBeforeStep()
          │
          ╳  Is failing (RuntimeException)
          │
          Given passing step with failing hook # FeatureContextAnnotations::passingStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAnnotations::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAnnotations::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        ┌─ @BeforeScenario @failing-before-hook # FeatureContextAnnotations::failingBeforeScenarioHook()
        │
        ╳  Is failing (RuntimeException)
        │
        @failing-before-hook
        Scenario:            # features/hooks-output.feature:12
          Given passing step # FeatureContextAnnotations::passingStep()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContextAnnotations::doSomethingAfterFeature()
      
      --- Failed hooks:
      
          BeforeStep passing step with failing hook "features/hooks-output.feature:9" # FeatureContextAnnotations::failingBeforeStep()
          BeforeScenario @failing-before-hook "features/hooks-output.feature:12" # FeatureContextAnnotations::failingBeforeScenarioHook()
      
      --- Skipped scenarios:
      
          features/hooks-output.feature:12
      
      --- Failed scenarios:
      
          features/hooks-output.feature:5 (on line 6)
          features/hooks-output.feature:8
      
      4 scenarios (1 passed, 2 failed, 1 skipped)
      4 steps (1 passed, 1 failed, 2 skipped)
      """

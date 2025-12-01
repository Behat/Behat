Feature: Hooks Attributes
  In order to hook into Behat testing process
  As a tester
  I need to be able to write hooks using attributes

  Background:
    Given I initialise the working directory from the "Hooks" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value                |
      | --no-colors |                      |
      | --config    | behat-attributes.php |

  Scenario: Basic hooks execution
    When I run behat with the following additional options:
      | option                | value |
      | features/test.feature |       |
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContextAttributes::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:             # features/test.feature:2
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 50 # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                 # features/test.feature:4
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given I have entered 12 # FeatureContextAttributes::iHaveEntered()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 12     # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @thirty
        Scenario:                # features/test.feature:9
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given I must have 30   # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          When I have entered 23 # FeatureContextAttributes::iHaveEntered()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 23    # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @100 @thirty
        Scenario:               # features/test.feature:14
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given I must have 30  # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          When I have entered 1 # FeatureContextAttributes::iHaveEntered()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 100  # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @filtered
        Scenario:                                                # features/test.feature:20
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have a scenario filter value of "filtered" # FeatureContextAttributes::iMustHaveScenarioFilter()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        @~filtered
        Scenario:                                                 # features/test.feature:24
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have a scenario filter value of "~filtered" # FeatureContextAttributes::iMustHaveScenarioFilter()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContextAttributes::doSomethingAfterFeature()
      
      6 scenarios (6 passed)
      11 steps (11 passed)
      """

  Scenario: Hooks on tagged features
    When I run behat with the following additional options:
      | option                | value |
      | features/some.feature |       |
    Then it should pass with:
      """
      ┌─ @BeforeFeature # FeatureContextAttributes::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      ┌─ @BeforeFeature @someFeature # FeatureContextAttributes::doSomethingBeforeSomeFeature()
      │
      │  = do something before SOME feature
      │
      @someFeature
      Feature:
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:             # features/some.feature:3
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Then I must have 50 # FeatureContextAttributes::iMustHave()
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContextAttributes::doSomethingAfterFeature()
      
      │
      │  = do something after SOME feature
      │
      └─ @AfterFeature @someFeature # FeatureContextAttributes::doSomethingAfterSomeFeature()
      
      1 scenario (1 passed)
      1 step (1 passed)
      """

  Scenario: Hooks output
    When I run behat with the following additional options:
      | option                        | value |
      | features/hooks-output.feature |       |
    Then it should fail with:
      """
      ┌─ @BeforeFeature # FeatureContextAttributes::doSomethingBeforeFeature()
      │
      │  = do something BEFORE EVERY FEATURE
      │
      Feature:
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/hooks-output.feature:2
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given passing step # FeatureContextAttributes::passingStep()
            │ Is passing
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:            # features/hooks-output.feature:5
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          Given failing step # FeatureContextAttributes::failingStep()
            Failing (RuntimeException)
          │
          │  Is passing
          │
          └─ @AfterStep # FeatureContextAttributes::passingAfterStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        Scenario:                              # features/hooks-output.feature:8
          ┌─ @BeforeStep # FeatureContextAttributes::passingBeforeStep()
          │
          │  Is passing
          │
          ┌─ @BeforeStep passing step with failing hook # FeatureContextAttributes::failingBeforeStep()
          │
          ╳  Is failing (RuntimeException)
          │
          Given passing step with failing hook # FeatureContextAttributes::passingStep()
        │
        │  Is passing
        │
        └─ @AfterScenario # FeatureContextAttributes::passingAfterScenarioHook()
      
        ┌─ @BeforeScenario # FeatureContextAttributes::passingBeforeScenarioHook()
        │
        │  Is passing
        │
        ┌─ @BeforeScenario @failing-before-hook # FeatureContextAttributes::failingBeforeScenarioHook()
        │
        ╳  Is failing (RuntimeException)
        │
        @failing-before-hook
        Scenario:            # features/hooks-output.feature:12
          Given passing step # FeatureContextAttributes::passingStep()
      
      │
      │  = do something AFTER EVERY FEATURE
      │
      └─ @AfterFeature # FeatureContextAttributes::doSomethingAfterFeature()
      
      --- Failed hooks:
      
          BeforeStep passing step with failing hook "features/hooks-output.feature:9" # FeatureContextAttributes::failingBeforeStep()
          BeforeScenario @failing-before-hook "features/hooks-output.feature:12" # FeatureContextAttributes::failingBeforeScenarioHook()
      
      --- Skipped scenarios:
      
          features/hooks-output.feature:12
      
      --- Failed scenarios:
      
          features/hooks-output.feature:5 (on line 6)
          features/hooks-output.feature:8
      
      4 scenarios (1 passed, 2 failed, 1 skipped)
      4 steps (1 passed, 1 failed, 2 skipped)
      """

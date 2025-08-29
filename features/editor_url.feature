Feature: Editor URL
  In order to be able to open files directly in my editor
  As a developer
  I need to be able to ask Behat to add editor links to file paths in the output

  Background:
    Given I initialise the working directory from the "EditorUrl" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |

  Scenario: Add option in command line
    When I run behat with the following additional options:
      | option       | value                                        |
      | --editor-url | 'phpstorm://open?file={relPath}&line={line}' |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=16>features/bootstrap/FeatureContext.php line 16</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
      """

  Scenario: Add option in config file
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | editor_url |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=16>features/bootstrap/FeatureContext.php line 16</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>features/test.feature:3</>
      """

  Scenario: Use absolute paths in editor URL
    When I run behat with the following additional options:
      | option       | value                                        |
      | --editor-url | 'phpstorm://open?file={absPath}&line={line}' |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=%%WORKING_DIR%%features/test.feature&line=3>features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=%%WORKING_DIR%%features/bootstrap/FeatureContext.php&line=16>features/bootstrap/FeatureContext.php line 16</>

      --- Failed scenarios:

          <href=phpstorm://open?file=%%WORKING_DIR%%features/test.feature&line=3>features/test.feature:3</>
      """

  Scenario: Use relative paths in url but absolute paths in visible text
    When I run behat with the following additional options:
      | option                 | value                                        |
      | --print-absolute-paths |                                              |
      | --editor-url           | 'phpstorm://open?file={relPath}&line={line}' |
    Then the output should contain:
      """
        Scenario:                                    # <href=phpstorm://open?file=features/test.feature&line=3>%%WORKING_DIR%%features/test.feature:3</>
          Given I have a passing step                # FeatureContext::iHaveAPassingStep()
          And I have a step that throws an exception # FeatureContext::iHaveAFailingStep()
            Warning: Undefined variable $b in <href=phpstorm://open?file=features/bootstrap/FeatureContext.php&line=16>%%WORKING_DIR%%features/bootstrap/FeatureContext.php line 16</>

      --- Failed scenarios:

          <href=phpstorm://open?file=features/test.feature&line=3>%%WORKING_DIR%%features/test.feature:3</>
      """

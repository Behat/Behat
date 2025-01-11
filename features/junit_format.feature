  Feature: JUnit Formatter
  In order to integrate with other development tools
  As a developer
  I need to be able to generate a JUnit-compatible report

  Background:
    Given I set the working directory to the "JunitFormat" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |
      | --snippets-type | regex            |
      | --format        | junit            |
      | --out           | {SYSTEM_TMP_DIR} |

    Scenario: Run a single feature
    When I run behat with the following additional options:
      | option         | value                |
      | --snippets-for | SingleFeatureContext |
      | --suite        | single_feature       |
    Then it should fail with:
      """
      --- SingleFeatureContext has missing steps. Define them with these snippets:

          #[Then('/^Something new$/')]
          public function somethingNew(): void
          {
              throw new PendingException();
          }
      """
    And the temp "single_feature.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="single_feature">
        <testsuite name="World consistency" tests="8" skipped="0" failures="3" errors="2" time="-IGNORE-VALUE-">
          <testcase name="Undefined" classname="World consistency" status="undefined" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature">
            <error message="And Something new" type="undefined"/>
          </testcase>
          <testcase name="Pending" classname="World consistency" status="pending" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature">
            <error message="And Something not done yet: TODO: write pending definition" type="pending"/>
          </testcase>
          <testcase name="Failed" classname="World consistency" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #1" classname="World consistency" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature">
            <failure message="Then I must have 16: Failed asserting that 15 matches expected '16'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #2" classname="World consistency" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature"/>
          <testcase name="Passed &amp; Failed #3" classname="World consistency" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature">
            <failure message="Then I must have 32: Failed asserting that 33 matches expected '32'."/>
          </testcase>
          <testcase name="Another Outline #1" classname="World consistency" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature"/>
          <testcase name="Another Outline #2" classname="World consistency" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature"/>
        </testsuite>
      </testsuites>
      """
    And the temp file "single_feature.xml" should be a valid document according to "junit.xsd"

  Scenario: Run multiple Features
    When I run behat with the following additional options:
      | option  | value             |
      | --suite | multiple_features |
    Then it should pass
    And the temp "multiple_features.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="multiple_features">
        <testsuite name="Adding Feature 1" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Adding 4 to 10" classname="Adding Feature 1" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_features_1.feature"></testcase>
        </testsuite>
        <testsuite name="Adding Feature 2" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Adding 8 to 10" classname="Adding Feature 2" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_features_2.feature"></testcase>
        </testsuite>
      </testsuites>
      """
    And the temp file "multiple_features.xml" should be a valid document according to "junit.xsd"

  Scenario: Confirm multiline scenario titles are printed correctly
    When I run behat with the following additional options:
      | option  | value            |
      | --suite | multiline_titles |
    Then it should pass with no output
    And the temp "multiline_titles.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="multiline_titles">
        <testsuite name="Use multiline titles" tests="2" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Adding some interesting value" classname="Use multiline titles" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiline_titles.feature"/>
          <testcase name="Subtracting some value" classname="Use multiline titles" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiline_titles.feature"/>
        </testsuite>
      </testsuites>
      """
    And the temp file "multiline_titles.xml" should be a valid document according to "junit.xsd"

  Scenario: Multiple suites
    When I run behat with the following additional options:
      | option   | value          |
      | --config | two_suites.php |
    Then it should fail with no output
    And the temp "small_kid.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="small_kid">
        <testsuite name="Apple Eating" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Eating one apple" classname="Apple Eating" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_suites_1.feature"/>
        </testsuite>
      </testsuites>
      """
    And the temp file "small_kid.xml" should be a valid document according to "junit.xsd"
    And the temp "old_man.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="old_man">
        <testsuite name="Apple Eating" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Eating one apple" classname="Apple Eating" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_suites_2.feature">
            <failure message="Then I will be stronger: Failed asserting that 0 is not equal to 0."/>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the temp file "old_man.xml" should be a valid document according to "junit.xsd"

  Scenario: Report skipped testcases
    Given I want to run the suite "skipped_test_cases"
    When I run behat with the following additional options:
      | option  | value              |
      | --suite | skipped_test_cases |
    And the temp "skipped_test_cases.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="skipped_test_cases">
        <testsuite name="Skipped test cases" tests="2" skipped="2" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Skipped" classname="Skipped test cases" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-skipped_test_cases.feature">
            <failure message="BeforeScenario: (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Another skipped" classname="Skipped test cases" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-skipped_test_cases.feature">
            <failure message="BeforeScenario: (Exception)" type="setup"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the temp file "skipped_test_cases.xml" should be a valid document according to "junit.xsd"

  Scenario: Stop on Failure
    Given I want to run the suite "stop_on_failure"
    When I run behat with the following additional options:
      | option  | value           |
      | --suite | stop_on_failure |
    Then it should fail with no output
    And the temp "stop_on_failure.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="stop_on_failure">
        <testsuite name="Stop on failure" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Failed" classname="Stop on failure" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-stop_on_failure.feature">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the temp file "stop_on_failure.xml" should be a valid document according to "junit.xsd"

  Scenario: Aborting due to PHP error
    When I run behat with the following additional options:
      | option  | value              |
      | --suite | abort_on_php_error |
    Then it should fail with:
    """
    cannot implement Foo - it is not an interface
    """
    And the temp "abort_on_php_error.xml" file xml should be like:
    """
    <?xml version="1.0" encoding="UTF-8"?>
    <testsuites name="abort_on_php_error"/>
    """

  Scenario: Aborting due invalid output path
    When I run "behat -o behat.php"
    Then it should fail with:
      """
      Directory expected for the `output_path` option, but a filename was given.
      """

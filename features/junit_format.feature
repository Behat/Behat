Feature: JUnit Formatter
  In order to integrate with other development tools
  As a developer
  I need to be able to generate a JUnit-compatible report

  Background:
    Given I initialise the working directory from the "JunitFormat" fixtures folder
    And I provide the following options for all behat invocations:
      | option          | value            |
      | --no-colors     |                  |
      | --snippets-type | regex            |
      | --format        | junit            |
      | --out           | {BASE_PATH}/logs |

  Scenario: Run a single feature
    When I run behat with the following additional options:
      | option         | value          |
      | --snippets-for | FeatureContext |
      | --suite        | single_feature |
    Then it should fail with:
      """
      --- FeatureContext has missing steps. Define them with these snippets:

          #[Then('/^Something new$/')]
          public function somethingNew(): void
          {
              throw new PendingException();
          }
      """
    And the "logs/single_feature.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="single_feature">
        <testsuite name="Adding numbers" tests="9" skipped="0" failures="3" errors="2" time="-IGNORE-VALUE-">
          <testcase name="Passed" classname="Adding numbers" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="11"></testcase>
          <testcase name="Undefined" classname="Adding numbers" status="undefined" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="16">
            <error message="And Something new" type="undefined"/>
          </testcase>
          <testcase name="Pending" classname="Adding numbers" status="pending" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="21">
            <error message="And Something not done yet: TODO: write pending definition" type="pending"/>
          </testcase>
          <testcase name="Failed" classname="Adding numbers" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="25">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #1" classname="Adding numbers" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="29">
            <failure message="Then I must have 16: Failed asserting that 15 matches expected '16'."/>
          </testcase>
          <testcase name="Passed &amp; Failed #2" classname="Adding numbers" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="29"/>
          <testcase name="Passed &amp; Failed #3" classname="Adding numbers" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="29">
            <failure message="Then I must have 32: Failed asserting that 33 matches expected '32'."/>
          </testcase>
          <testcase name="Another Outline #1" classname="Adding numbers" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="39"/>
          <testcase name="Another Outline #2" classname="Adding numbers" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-single_feature.feature" line="39"/>
        </testsuite>
      </testsuites>
      """
    And the file "logs/single_feature.xml" should be a valid document according to "junit.xsd"

  Scenario: Run multiple Features
    When I run behat with the following additional options:
      | option  | value             |
      | --suite | multiple_features |
    Then it should pass with no output
    And the "logs/multiple_features.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="multiple_features">
        <testsuite name="Adding Feature 1" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Adding 4 to 10" classname="Adding Feature 1" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_features_1.feature" line="9"></testcase>
        </testsuite>
        <testsuite name="Adding Feature 2" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Adding 8 to 10" classname="Adding Feature 2" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_features_2.feature" line="9"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "logs/multiple_features.xml" should be a valid document according to "junit.xsd"

  Scenario: Confirm multiline scenario titles are printed correctly
    When I run behat with the following additional options:
      | option  | value            |
      | --suite | multiline_titles |
    Then it should pass with no output
    And the "logs/multiline_titles.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="multiline_titles">
        <testsuite name="Use multiline titles" tests="2" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Adding some interesting value" classname="Use multiline titles" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiline_titles.feature" line="13"/>
          <testcase name="Adding another value" classname="Use multiline titles" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiline_titles.feature" line="20"/>
        </testsuite>
      </testsuites>
      """
    And the file "logs/multiline_titles.xml" should be a valid document according to "junit.xsd"

  Scenario: Multiple suites
    When I run behat with the following additional options:
      | option   | value          |
      | --config | two_suites.php |
    Then it should fail with no output
    And the "logs/small_kid.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="small_kid">
        <testsuite name="Adding easy numbers" tests="1" skipped="0" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Easy sum" classname="Adding easy numbers" status="passed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_suites_1.feature" line="11"/>
        </testsuite>
      </testsuites>
      """
    And the file "logs/small_kid.xml" should be a valid document according to "junit.xsd"
    And the "logs/old_man.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="old_man">
        <testsuite name="Adding difficult numbers" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Difficult sum" classname="Adding difficult numbers" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-multiple_suites_2.feature" line="11">
            <failure message="Then I must have 477: Failed asserting that 378 matches expected '477'."/>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "logs/old_man.xml" should be a valid document according to "junit.xsd"

  Scenario: Report skipped testcases
    When I run behat with the following additional options:
      | option  | value              |
      | --suite | skipped_test_cases |
    Then it should fail with no output
    And the "logs/skipped_test_cases.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="skipped_test_cases">
        <testsuite name="Skipped test cases" tests="2" skipped="2" failures="0" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Skipped" classname="Skipped test cases" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-skipped_test_cases.feature" line="11">
            <failure message="BeforeScenario: This scenario has a failed setup (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Another skipped" classname="Skipped test cases" status="skipped" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-skipped_test_cases.feature" line="15">
            <failure message="BeforeScenario: This scenario has a failed setup (Exception)" type="setup"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "logs/skipped_test_cases.xml" should be a valid document according to "junit.xsd"

  Scenario: Stop on Failure
    When I run behat with the following additional options:
      | option  | value           |
      | --suite | stop_on_failure |
    Then it should fail with no output
    And the "logs/stop_on_failure.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="stop_on_failure">
        <testsuite name="Stop on failure" tests="1" skipped="0" failures="1" errors="0" time="-IGNORE-VALUE-">
          <testcase name="Failed" classname="Stop on failure" status="failed" time="-IGNORE-VALUE-" file="features-DIRECTORY-SEPARATOR-stop_on_failure.feature" line="11">
            <failure message="Then I must have 13: Failed asserting that 14 matches expected '13'."/>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "logs/stop_on_failure.xml" should be a valid document according to "junit.xsd"

  Scenario: Aborting due to PHP error
    When I run behat with the following additional options:
      | option  | value              |
      | --suite | abort_on_php_error |
    Then it should fail with:
    """
    cannot extend interface Behat\Behat\Context\Context
    """
    And the "logs/abort_on_php_error.xml" file xml should be like:
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

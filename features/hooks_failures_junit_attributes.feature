Feature: Display hook failures location in junit printer using attributes
  In order to be able to locate the code that generated a failure
  As a feature developer using the junit printer
  When a hook throws an error I want to see the related item where the code failed using attributes

  Background:
    Given I initialise the working directory from the "HookFailures" fixtures folder
    And I provide the following options for all behat invocations:
      | option      | value |
      | --no-colors |       |
      | --format    | junit |
      | --out       | junit |

  Scenario: Handling of a error in beforeSuite hook
    When I run behat with the following additional options:
      | option    | value       |
      | --profile | beforeSuite |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="2" failures="0" errors="0">
          <testcase name="First scenario" classname="First feature" status="skipped" file="features-DIRECTORY-SEPARATOR-one.feature" line="5">
            <failure message="BeforeSuite: Error in beforeSuite hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="skipped" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="1" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="skipped" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterSuite hook
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | afterSuite |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="5"></testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4">
            <failure message="AfterSuite: Error in afterSuite hook (Exception)" type="teardown"></failure>
          </testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in beforeFeature hook
    When I run behat with the following additional options:
      | option    | value         |
      | --profile | beforeFeature |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="2" failures="0" errors="0">
          <testcase name="First scenario" classname="First feature" status="skipped" file="features-DIRECTORY-SEPARATOR-one.feature" line="5">
            <failure message="BeforeFeature: Error in beforeFeature hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="skipped" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterFeature hook
    When I run behat with the following additional options:
      | option    | value        |
      | --profile | afterFeature |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="5"></testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="8">
            <failure message="AfterFeature: Error in afterFeature hook (Exception)" type="teardown"></failure>
          </testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in beforeScenario hook
    When I run behat with the following additional options:
      | option    | value          |
      | --profile | beforeScenario |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="1" failures="0" errors="0">
          <testcase name="First scenario" classname="First feature" status="skipped" file="features-DIRECTORY-SEPARATOR-one.feature" line="5">
            <failure message="BeforeScenario: Error in beforeScenario hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterScenario hook
    When I run behat with the following additional options:
      | option    | value         |
      | --profile | afterScenario |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="5">
            <failure message="AfterScenario: Error in afterScenario hook (Exception)" type="teardown"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in beforeStep hook
    When I run behat with the following additional options:
      | option    | value      |
      | --profile | beforeStep |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="0" failures="1" errors="0">
          <testcase name="First scenario" classname="First feature" status="failed" file="features-DIRECTORY-SEPARATOR-one.feature" line="5">
            <failure message="BeforeStep: When I have a simple step: Error in beforeStep hook (Exception)" type="setup"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

  Scenario: Handling of a error in afterStep hook
    When I run behat with the following additional options:
      | option    | value     |
      | --profile | afterStep |
    Then it should fail
    And "junit/default.xml" file xml should be like:
      """
      <?xml version="1.0" encoding="UTF-8"?>
      <testsuites name="default">
        <testsuite name="First feature" file="features-DIRECTORY-SEPARATOR-one.feature" tests="2" skipped="0" failures="1" errors="0">
          <testcase name="First scenario" classname="First feature" status="failed" file="features-DIRECTORY-SEPARATOR-one.feature" line="5">
            <failure message="AfterStep: When I have a simple step: Error in afterStep hook (Exception)" type="teardown"></failure>
          </testcase>
          <testcase name="Second scenario" classname="First feature" status="passed" file="features-DIRECTORY-SEPARATOR-one.feature" line="8"></testcase>
        </testsuite>
        <testsuite name="Second feature" file="features-DIRECTORY-SEPARATOR-two.feature" tests="1" skipped="0" failures="0" errors="0">
          <testcase name="First scenario" classname="Second feature" status="passed" file="features-DIRECTORY-SEPARATOR-two.feature" line="4"></testcase>
        </testsuite>
      </testsuites>
      """
    And the file "junit/default.xml" should be a valid document according to "junit.xsd"

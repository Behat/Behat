Feature: MCP Server
  In order to use Behat as an MCP server
  As a developer
  I need to be able to start an MCP server and communicate with it

  Background:
    Given I initialise the working directory from the "McpServer" fixtures folder

  Scenario: Initialize MCP server and run tests
    Given I start the MCP server
    When I send an MCP initialize request
    Then I should receive a successful MCP initialize response
    When I call the MCP tool "run-behat-tests"
    Then I should receive a successful tool response with 8 tests and 0 failures

  Scenario: Run tests with specific config file
    Given I start the MCP server
    When I send an MCP initialize request
    Then I should receive a successful MCP initialize response
    When I call the MCP tool "run-behat-tests" with config "behat-simple.php"
    Then I should receive a successful tool response with 3 tests and 0 failures

  Scenario: Run tests with specific profile
    Given I start the MCP server
    When I send an MCP initialize request
    Then I should receive a successful MCP initialize response
    When I call the MCP tool "run-behat-tests" with profile "calculator"
    Then I should receive a successful tool response with 7 tests and 0 failures

  Scenario: Run tests with specific suite
    Given I start the MCP server
    When I send an MCP initialize request
    Then I should receive a successful MCP initialize response
    When I call the MCP tool "run-behat-tests" with suite "greeting"
    Then I should receive a successful tool response with 1 tests and 0 failures

  Scenario: Run tests with specific paths
    Given I start the MCP server
    When I send an MCP initialize request
    Then I should receive a successful MCP initialize response
    When I call the MCP tool "run-behat-tests" with paths "features/greeting.feature,features/calculator.feature:6"
    Then I should receive a successful tool response with 4 tests and 0 failures

  Scenario: Run tests with additional options
    Given I start the MCP server
    When I send an MCP initialize request
    Then I should receive a successful MCP initialize response
    When I call the MCP tool "run-behat-tests" with additional options "--dry-run=true"
    Then I should receive a successful tool response with 8 tests and 8 skipped

  Scenario: Initialize MCP server with HTTP transport and run tests
    Given I start the MCP server with HTTP transport
    When I send an HTTP MCP initialize request
    Then I should receive a successful HTTP MCP initialize response
    When I call the HTTP MCP tool "run-behat-tests"
    Then I should receive a successful HTTP tool response with 8 tests and 0 failures

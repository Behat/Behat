Feature: Digest Formatter
  In order to have an overview of the project features
  As a feature writer
  I need to have digest formatter

  Scenario: Simple
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\CustomSnippetAcceptingContext,
          Behat\Behat\Tester\Exception\PendingException;
      use Behat\Gherkin\Node\PyStringNode,
          Behat\Gherkin\Node\TableNode;

      class FeatureContext implements CustomSnippetAcceptingContext
      {
          public static function getAcceptedSnippetType() { return 'regex'; }
      }
      """
    Given a file named "features/digest.feature" with:
      """
      Feature: Provide a digest of my features
        In order to easily browse my application's features
        As a features developer
        I want to have access to a digest of my features

        Scenario: It starts here
          Given something
          When something new
          Then something should have happened

        Scenario: It continues here
          Given something doesn't exist
          When something else
          Then something or not

        Scenario: It ends here
          When nothing
          Then void
      """
    When I run "behat --no-colors -f digest --no-snippets"
    Then it should pass with:
      """
      Feature Provide a digest of my features features/digest.feature
       6 It starts here
       11 It continues here
       16 It ends here
      """

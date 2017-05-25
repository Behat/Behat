Feature:
  In order to have simple step definitions
  As a step definition writer
  I want to be able to inject previously created objects directly into the steps

  Scenario: Returned variable is injectable into future contexts
    Given a file named "features/bootstrap/FeatureContext.php" with:
      """
      <?php

      use Behat\Behat\Context\Context;

      class Bucket
      {
          private $name;

          public static function named($name)
          {
              $bucket = new self();
              $bucket->name = $name;

              return $bucket;
          }

          public function getName()
          {
              return $this->name;
          }
      }

      class FeatureContext implements Context
      {
          /**
           * @Given there is a bucket named :name
           */
          public function iCreateABucketNamed($name)
          {
              return Bucket::named($name);
          }

          /**
           * @Then the bucket should be named :name
           */
          public function iAddTheTokenToThisBucket(Bucket $bucket, $name)
          {
              if ($bucket->getName() != $name) {
                  throw new \RuntimeException(sprintf(
                      'Expected name "%s" but got "%s"',
                      $name,
                      $bucket->getName()
                  ));
              }
          }
      }
      """
    And a file named "features/context_sharing.feature" with:
      """
      Feature:
        Scenario:
          Given there is a bucket named "ContinuousPipe"
          Then the bucket should be named "ContinuousPipe"
      """
    When I run "behat --no-colors -f progress features/context_sharing.feature "
    Then it should pass with:
      """
      ..

      1 scenario (1 passed)
      2 steps (2 passed)
      """

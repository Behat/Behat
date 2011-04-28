Feature: HTML Formatter
  In order to print features
  As a feature writer
  I need to have an html formatter

  Background:
    Given a file named "features/support/bootstrap.php" with:
      """
      <?php
      require_once 'PHPUnit/Autoload.php';
      require_once 'PHPUnit/Framework/Assert/Functions.php';
      """

  Scenario: Multiple parameters
    Given a file named "features/steps/math.php" with:
      """
      <?php
      $steps->Given('/I have entered (\d+)/', function($world, $num) {
          assertObjectNotHasAttribute('value', $world);
          $world->value = $num;
      });

      $steps->Then('/I must have (\d+)/', function($world, $num) {
          assertEquals($num, $world->value);
      });

      $steps->When('/I (add|subtract) the value (\d+)/', function($world, $op, $num) {
          if ($op == 'add')
            $world->value += $num;
          elseif ($op == 'subtract')
            $world->value -= $num;
      });
      """
    And a file named "features/World.feature" with:
      """
      Feature: World consistency
        In order to maintain stable behaviors
        As a features developer
        I want, that "World" flushes between scenarios

        Background:
          Given I have entered 10

        Scenario: Adding
          Then I must have 10
          And I add the value 6
          Then I must have 16

        Scenario: Subtracting
          Then I must have 10
          And I subtract the value 6
          Then I must have 4
      """
    When I run "behat -f html"
    Then it should pass with:
      """
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns ="http://www.w3.org/1999/xhtml">
      <head>
          <meta content="text/html;charset=utf-8"/>
          <title>Behat Test Suite</title>
          <link href="http://fonts.googleapis.com/css?family=Lobster" rel="stylesheet" type="text/css"/>
          <style type="text/css">
              body {
                  margin:0px;
                  padding:0px;
                  position:relative;
              }
              #behat {
                  float:left;
                  font-family: Georgia, serif;
                  font-size:18px;
                  line-height:26px;
              }
              #behat .statistics {
                  float:left;
                  width:100%;
                  margin-bottom:15px;
              }
              #behat .statistics:before {
                  content:'Behat';
                  position:absolute;
                  color: #1C4B20 !important;
                  text-shadow: white 1px 1px 1px;
                  font-size:48px !important;
                  font-family: Lobster, Tahoma;
                  top:22px;
                  left:20px;
              }
              #behat .statistics p {
                  text-align:right;
                  padding:5px 15px;
                  margin:0px;
                  border-right:10px solid #000;
              }
              #behat .statistics.failed p {
                  border-color:#C20000;
              }
              #behat .statistics.passed p {
                  border-color:#3D7700;
              }
              #behat .feature {
                  margin:15px;
              }
              #behat h2, #behat h3, #behat h4 {
                  margin:0px 0px 5px 0px;
                  padding:0px;
                  font-family:Georgia;
              }
              #behat h2 .title, #behat h3 .title, #behat h4 .title {
                  font-weight:normal;
              }
              #behat .path {
                  font-size:10px;
                  font-weight:normal;
                  font-family: 'Bitstream Vera Sans Mono', 'DejaVu Sans Mono', Monaco, Courier, monospace !important;
                  color:#999;
                  padding:0px 5px;
                  float:right;
              }
              #behat h3 .path {
                  margin-right:4%;
              }
              #behat ul.tags {
                  font-size:14px;
                  font-weight:bold;
                  color:#246AC1;
                  list-style:none;
                  margin:0px;
                  padding:0px;
              }
              #behat ul.tags li {
                  display:inline;
              }
              #behat ul.tags li:after {
                  content:' ';
              }
              #behat ul.tags li:last-child:after {
                  content:'';
              }
              #behat .feature > p {
                  margin-top:0px;
                  margin-left:20px;
              }
              #behat .scenario {
                  margin-left:20px;
                  margin-bottom:40px;
              }
              #behat .scenario > ol {
                  margin:0px;
                  list-style:none;
                  margin-left:20px;
                  padding:0px;
              }
              #behat .scenario > ol:after {
                  content:'';
                  display:block;
                  clear:both;
              }
              #behat .scenario > ol li {
                  float:left;
                  width:95%;
                  padding-left:5px;
                  border-left:5px solid;
                  margin-bottom:4px;
              }
              #behat .scenario > ol li .argument {
                  margin:10px 20px;
                  font-size:16px;
              }
              #behat .scenario > ol li table.argument {
                  border:1px solid #d2d2d2;
              }
              #behat .scenario > ol li table.argument thead td {
                  font-weight: bold;
              }
              #behat .scenario > ol li table.argument td {
                  padding:5px 10px;
                  background:#f3f3f3;
              }
              #behat .scenario > ol li .keyword {
                  font-weight:bold;
              }
              #behat .scenario > ol li .path {
                  float:right;
              }
              #behat .scenario .examples {
                  margin-top:20px;
                  margin-left:40px;
              }
              #behat .scenario .examples table {
                  margin-left:20px;
              }
              #behat .scenario .examples table thead td {
                  font-weight:bold;
                  text-align:center;
              }
              #behat .scenario .examples table td {
                  padding:2px 10px;
                  font-size:16px;
              }
              #behat .scenario .examples table .failed.exception td {
                  border-left:5px solid #000;
                  border-color:#C20000 !important;
                  padding-left:0px;
              }
              pre {
                  font-family:monospace;
              }
              .snippet {
                  font-size:14px;
                  color:#000;
                  margin-left:20px;
              }
              .backtrace {
                  font-size:12px;
                  color:#C20000;
                  overflow:hidden;
                  margin-left:20px;
              }
              #behat .passed {
                  background:#DBFFB4;
                  border-color:#65C400 !important;
                  color:#3D7700;
              }
              #behat .failed {
                  background:#FFFBD3;
                  border-color:#C20000 !important;
                  color:#C20000;
              }
              #behat .undefined, #behat .pending {
                  border-color:#FAF834 !important;
                  background:#FCFB98;
                  color:#000;
              }
              #behat .skipped {
                  background:lightCyan;
                  border-color:cyan !important;
                  color:#000;
              }
          </style>
      </head>
      <body>
          <div id="behat">
      
      <div class="feature">
      <h2>
      <span class="keyword">Feature: </span>
      <span class="title">World consistency</span>
      </h2>
      <p>
      In order to maintain stable behaviors<br />
      As a features developer<br />
      I want, that &quot;World&quot; flushes between scenarios<br />
      </p>
      
      <div class="scenario background">
      <h3>
      <span class="keyword">Background: </span>
      <span class="path">features/World.feature:6</span>
      </h3>
      <ol>
      <li class="passed">
      <div class="step">
      <span class="keyword">Given </span>
      <span class="text">I have entered <strong class="passed_param">10</strong></span>
      <span class="path">features/steps/math.php:5</span>
      </div>
      </li>
      </ol>
      </div>
      <div class="scenario">
      <h3>
      <span class="keyword">Scenario: </span>
      <span class="title">Adding</span>
      <span class="path">features/World.feature:9</span>
      </h3>
      <ol>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">10</strong></span>
      <span class="path">features/steps/math.php:9</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">And </span>
      <span class="text">I <strong class="passed_param">add</strong> the value <strong class="passed_param">6</strong></span>
      <span class="path">features/steps/math.php:16</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">16</strong></span>
      <span class="path">features/steps/math.php:9</span>
      </div>
      </li>
      </ol>
      </div>
      <div class="scenario">
      <h3>
      <span class="keyword">Scenario: </span>
      <span class="title">Subtracting</span>
      <span class="path">features/World.feature:14</span>
      </h3>
      <ol>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">10</strong></span>
      <span class="path">features/steps/math.php:9</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">And </span>
      <span class="text">I <strong class="passed_param">subtract</strong> the value <strong class="passed_param">6</strong></span>
      <span class="path">features/steps/math.php:16</span>
      </div>
      </li>
      <li class="passed">
      <div class="step">
      <span class="keyword">Then </span>
      <span class="text">I must have <strong class="passed_param">4</strong></span>
      <span class="path">features/steps/math.php:9</span>
      </div>
      </li>
      </ol>
      </div>
      </div>
      
          </div>
      </body>
      </html>
      """

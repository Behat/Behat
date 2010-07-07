<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$this->Given('/^Debug$/', array($this, 'stepDebug'));
$this->Given('/^I am on(?: the)* (.+)$/', array($this, 'stepIAmOnThe'));

$this->When('/^I follow|click(?: the)* "([^\"]*)"(?: link)*$/', array($this, 'stepIFollowTheLink'));

$this->When('/^I fill in "([^\"]*)" with "([^\"]*)"$/', array($this, 'stepIFillInWith'));
$this->When('/^I select "([^\"]*)" from "([^\"]*)"$/', array($this, 'stepISelectFrom'));
$this->When('/^I check "([^\"]*)"$/', array($this, 'stepICheck'));
$this->When('/^I uncheck "([^\"]*)"$/', array($this, 'stepIUncheck'));
$this->When('/^I attach the file at "([^\"]*)" to "([^\"]*)"$/', array($this, 'stepIAttachTheFileAtTo'));
$this->When('/^I press "([^\"]*)"$/', array($this, 'stepIPress'));

$this->Then('/^I should see "(.*)"$/', array($this, 'stepIShouldSee'));
$this->Then('/^I should not see "(.*)"$/', array($this, 'stepIShouldNotSee'));

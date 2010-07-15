<?php

$steps->Given('/^I am on(?: the)? (.*)$/', function($page) use($world) {
    $world->visit($world->pathTo($page));
});

$steps->Given('/I (?:follow|click)(?: the)? "([^"]*)"(?: link)*/', function($a) use($world) {
    $link = $world->response->selectLink($a)->link();
    $world->response = $world->client->click($link);
});

$steps->When('/^I fill in "([^"]*)" with "([^"]*)"$/', function($fld, $val) use($world) {
    $world->form[$fld] = $val;
});

$steps->When('/^I select "([^"]*)" from "([^"]*)"$/', function($val, $fld) use($world) {
    $world->form[$fld] = $val;
});

$steps->When('/^I check "([^"]*)"$/', function($fld) use($world) {
    $world->form[$fld] = true;
});

$steps->When('/^I uncheck "([^"]*)"$/', function($fld) use($world) {
    $world->form[$fld] = false;
});

$steps->When('/^I attach the file at "([^"]*)" to "([^"]*)"$/', function($path, $fld) use($world) {
    $world->form[$fld] = $path;
});

$steps->When('/^I press "([^"]*)"$/', function($fld) use($world) {
    $form = $world->response->selectButton($button);
    $world->response = $world->client->submit($form, $world->form);
    $world->form = array();
});

$steps->Then('/^I should see "([^"]*)"$/', function($text) use($world) {
    assertContains($text, $world->response->text());
});

$steps->Then('/^I should not see "([^"]*)"$/', function($text) use($world) {
    assertNotContains($text, $world->response->text());
});

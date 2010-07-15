<?php

$steps->Допустим('/^Я на(?: странице)? (.*)$/', function($page) use($world) {
    $world->visit($world->pathTo($page));
});

$steps->Если('/^Я (?:кликаю|перехожу) по ссылке "([^"]*)"$/', function($a) use($world) {
    $link = $world->response->selectLink($a)->link();
    $world->response = $world->client->click($link);
});

$steps->Если('/^Я заполняю поле "([^"]*)" значением "([^"]*)"$/', function($fld, $val) use($world) {
    $world->form[$fld] = $val;
});

$steps->Если('/^Я (?:выбираю|ставлю) "([^"]*)" в поле "([^"]*)"$/', function($val, $fld) use($world) {
    $world->form[$fld] = $val;
});

$steps->Если('/^Я (?:отмечаю|помечаю) "([^"]*)"$/', function($fld) use($world) {
    $world->form[$fld] = true;
});

$steps->Если('/^Я снимаю отметку с "([^"]*)"$/', function($fld) use($world) {
    $world->form[$fld] = false;
});

$steps->Если('/^Я выбираю файл "([^"]*)" в поле "([^"]*)"$/', function($path, $fld) use($world) {
    $world->form[$fld] = $path;
});

$steps->Если('/^Я (?:нажимаю|кликаю)(?: по)? "([^"]*)"$/', function($fld) use($world) {
    $form = $world->response->selectButton($button);
    $world->response = $world->client->submit($form, $world->form);
    $world->form = array();
});

$steps->То('/^Я должен увидеть "([^"]*)"$/', function($text) use($world) {
    assertContains($text, $world->response->text());
});

$steps->То('/^Я не должен увидеть "([^"]*)"$/', function($text) use($world) {
    assertNotContains($text, $world->response->text());
});

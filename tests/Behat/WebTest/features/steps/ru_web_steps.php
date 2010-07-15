<?php

$steps->Допустим('/^Я на странице (.*)$/', function($page) use($world) {
    $world->visit($world->pathTo($page));
});

$steps->Если('/^Я кликаю по ссылке "([^"]*)"$/', function($a) use($world) {
    $link = $world->response->selectLink($a)->link();
    $world->response = $world->client->click($link);
});

$steps->То('/^Я должен увидеть "([^"]*)"$/', function($text) use($world) {
    assertContains($text, $world->response->text());
});



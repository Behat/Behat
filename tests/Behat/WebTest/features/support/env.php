<?php

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';
require 'paths.php';

// Create WebClient behavior
$this->client = new \Goutte\Client;
$this->response = null;
$this->form = array();

// Helpful closures
$this->visit = function($link) use($world) {
    $world->response = $world->client->request('GET', $link);
};

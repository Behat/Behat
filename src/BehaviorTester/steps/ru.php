<?php

/*
 * This file is part of the BehaviorTester.
 * (c) 2010 Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$this->Допустим('/^Я на(?: странице)* (.+)$/', array($this, 'stepIAmOnThe'));

$this->Если('/^Я перехожу|кликаю(?: по)*(?: ссылке)* "([^\"]*)"$/', array($this, 'stepIFollowTheLink'));

$this->Если('/^Я заполняю поле "([^\"]*)" значением "([^\"]*)"$/', array($this, 'stepIFillInWith'));
$this->Если('/^Я выбираю "([^\"]*)" в поле "([^\"]*)"$/', array($this, 'stepISelectFrom'));
$this->Если('/^Я отмечаю "([^\"]*)"$/', array($this, 'stepICheck'));
$this->Если('/^Я снимаю отметку с "([^\"]*)"$/', array($this, 'stepIUncheck'));
$this->Если('/^Я выбираю файл "([^\"]*)" в поле "([^\"]*)"$/', array($this, 'stepIAttachTheFileAtTo'));
$this->Если('/^Я нажимаю "([^\"]*)"$/', array($this, 'stepIPress'));

$this->То('/^Я должен увидеть "(.*)"$/', array($this, 'stepIShouldSee'));
$this->То('/^Я не должен увидеть "(.*)"$/', array($this, 'stepIShouldNotSee'));

<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\Event;

use Symfony\Component\Translation\Translator;

interface FormatterInterface
{
    function __construct(Translator $translator);

    function setParameter($name, $value);

    function getParameter($name);

    function afterSuite(Event $event);

    function beforeSuite(Event $event);

    function beforeFeature(Event $event);

    function afterFeature(Event $event);

    function beforeBackground(Event $event);

    function afterBackground(Event $event);

    function beforeOutline(Event $event);

    function beforeOutlineExample(Event $event);

    function afterOutlineExample(Event $event);

    function afterOutline(Event $event);

    function beforeScenario(Event $event);

    function afterScenario(Event $event);

    function beforeStep(Event $event);

    function afterStep(Event $event);
}

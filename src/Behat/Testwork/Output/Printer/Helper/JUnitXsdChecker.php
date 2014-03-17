<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer\Helper;

/**
 * A class for checking if attributes or elements comply to the JUnit XSD.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class JUnitXsdChecker
{
    /**
     * Validates the attributes of the <testsuites> element.
     *
     * @param array $attributes
     *
     * @see checkAttributes
     */
    public function validateTestsuitesAttributes(array $attributes)
    {
        $this->checkAttributes($attributes, array('name', 'time', 'tests', 'failures', 'disabled', 'errors'), 'testsuites');
    }

    /**
     * Validates the attributes of the <testsuite> element.
     *
     * @param array $attributes
     *
     * @see checkAttributes
     */
    public function validateTestsuiteAttributes(array $attributes)
    {
        $this->checkAttributes($attributes, array('name', 'tests', 'failures', 'errors', 'time', 'disabled', 'skipped', 'timestamp', 'hostname', 'id', 'package'), 'testsuite');
    }

    /**
     * Validates the attributes of the <testcase> element.
     *
     * @param array $attributes
     *
     * @see checkAttributes
     */
    public function validateTestcaseAttributes(array $attributes)
    {
        $this->checkAttributes($attributes, array('name', 'assertions', 'time', 'classname', 'status'), 'testsuite');
    }

    /**
     * Validates the children elements of the <testcase> element.
     *
     * @param array $nodeName
     *
     * @throws \Logicexception When the nodename is invalid
     */
    public function validateTestcaseChildNodeName($nodeName)
    {
        $validNodeNames = array('skipped', 'error', 'failure', 'system-out', 'system-err');
        if (!in_array($nodeName, $validNodeNames)) {
            throw new \LogicException(sprintf(
                'Invalid node name (%s) given for a child of the <testcase> element, supported node names: %s',
                $nodeName,
                implode(', ', $validNodeNames)
            ));
        }
    }

    /**
     * Validates if a child element of the <testcase> element is allowed to 
     * have a node value.
     *
     * @param string      $nodeName
     * @param string|null $nodeValue
     */
    public function validateTestcaseChildNodeValue($nodeName, $nodeValue = null)
    {
        if (null === $nodeValue) {
            return;
        }

        $canHaveAValue = false;
        switch ($nodeName) {
            case 'error':
            case 'failure':
                break;

            case 'system-out':
            case 'system-err':
            case 'skipped':
                $canHaveAValue = true;
                break;
        }

        if (!$canHaveAValue) {
            throw new \LogicException(sprintf(
                'Element <%s> cannot have a value, got "%s"',
                $nodeName,
                $nodeValue
            ));
        }
    }

    /**
     * Validates the attributes of a child of the <testcase> element.
     *
     * @param string $nodeName
     * @param array  $nodeAttributes
     *
     * @see checkAttributes
     */
    public function validateTestcaseChildAttributes($nodeName, array $nodeAttributes)
    {
        $supportedAttributes = array();
        switch ($nodeName) {
            case 'skipped':
            case 'system-err':
            case 'system-out':
                break;

            case 'error':
            case 'failure':
                $supportedAttributes = array('type', 'message');
                break;
        }

        $this->checkAttributes($nodeAttributes, $supportedAttributes, $nodeName);
    }

    /**
     * Checks if there are unsupported attributes.
     *
     * @param array  $actualAttributes
     * @param array  $supportedAttributes
     * @param string $elementName
     *
     * @throws \LogicException When there are invalid attributes
     */
    protected function checkAttributes(array $actualAttributes, array $supportedAttributes, $elementName)
    {
        $invalidAttributes = array();
        foreach ($actualAttributes as $attributeName => $attributeValue) {
            if (!in_array($attributeName, $supportedAttributes)) {
                $invalidAttributes[] = $attributeName;
            }
        }

        if (0 !== count($invalidAttributes)) {
            throw new \LogicException(sprintf(
                'Invalid attributes (%s) given for <%s> element, supported attributes: %s',
                implode(', ', $invalidAttributes),
                $elementName,
                implode(', ', $supportedAttributes)
            ));
        }
    }
}

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
     * Validates the attributes for the <testsuites> element.
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
     * Validates the attributes for the <testsuite> element.
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
     * Validates the attributes for the <testcase> element.
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
     * Validates the children elements for the <testcase> element.
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

        $this->checkAttributes($nodeAttributes, $supportedAttributes);
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
            if (!in_array($attribute)) {
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

<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Printer\Factory\FilesystemOutputFactory;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A convenient wrapper around the ConsoleOutputPrinter to write valid JUnit
 * reports.
 *
 * @author Wouter J <wouter@wouterj.nl>
 * @author James Watson <james@sitepulse.org>
 */
final class JUnitOutputPrinter extends StreamOutputPrinter
{
    const XML_VERSION  = '1.0';
    const XML_ENCODING = 'UTF-8';

    /**
     * @var \DOMDocument
     */
    private $domDocument;
    /**
     * @var \DOMElement
     */
    private $currentTestsuite;
    /**
     * @var \DOMElement
     */
    private $currentTestcase;
    /**
     * @var \DOMElement
     */
    private $testSuites;

    public function __construct(FilesystemOutputFactory $outputFactory)
    {
        parent::__construct($outputFactory);
    }

    /**
     * Creates a new JUnit file.
     *
     * The file will be initialized with an XML definition and the root element.
     *
     * @param string $name                 The filename (without extension) and default value of the name attribute
     * @param array  $testsuitesAttributes Attributes for the root element
     */
    public function createNewFile($name, array $testsuitesAttributes = array())
    {
        $this->setFileName(strtolower(trim(preg_replace('/[^[:alnum:]_]+/', '_', $name), '_')));

        $this->domDocument = new \DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $this->domDocument->formatOutput = true;

        $this->testSuites = $this->domDocument->createElement('testsuites');
        $this->domDocument->appendChild($this->testSuites);
        $this->addAttributesToNode($this->testSuites, array_merge(array('name' => $name), $testsuitesAttributes));
        $this->flush();
    }

    /**
     * Adds a new <testsuite> node.
     *
     * @param array $testsuiteAttributes
     */
    public function addTestsuite(array $testsuiteAttributes = array())
    {
        $this->currentTestsuite = $this->domDocument->createElement('testsuite');
        $this->testSuites->appendChild($this->currentTestsuite);
        $this->addAttributesToNode($this->currentTestsuite, $testsuiteAttributes);
    }


    /**
     * Adds a new <testcase> node.
     *
     * @param array $testcaseAttributes
     */
    public function addTestcase(array $testcaseAttributes = array())
    {
        $this->currentTestcase = $this->domDocument->createElement('testcase');
        $this->currentTestsuite->appendChild($this->currentTestcase);
        $this->addAttributesToNode($this->currentTestcase, $testcaseAttributes);
    }

    /**
     * Add a testcase child element.
     *
     * @param string $nodeName
     * @param array  $nodeAttributes
     * @param string $nodeValue
     */
    public function addTestcaseChild($nodeName, array $nodeAttributes = array(), $nodeValue = null)
    {
        $childNode = $this->domDocument->createElement($nodeName, $nodeValue);
        $this->currentTestcase->appendChild($childNode);
        $this->addAttributesToNode($childNode, $nodeAttributes);
    }

    private function addAttributesToNode(\DOMElement $node, array $attributes)
    {
        foreach ($attributes as $name => $value){
            $node->setAttribute($name, $value);
        }
    }

    /**
     * Sets file name.
     *
     * @param string $fileName
     * @param string $extension The file extension, defaults to "xml"
     */
    public function setFileName($fileName, $extension = 'xml')
    {
        if ('.'.$extension !== substr($fileName, strlen($extension) + 1)) {
            $fileName .= '.'.$extension;
        }

        $this->getOutputFactory()->setFileName($fileName);
        $this->flush();
    }

    /**
     * Generate XML from the DOMDocument and parse to the the writing stream
     */
    public function flush()
    {
        if($this->domDocument instanceof \DOMDocument){
            $this->getWritingStream()->write(
                $this->domDocument->saveXML(null, LIBXML_NOEMPTYTAG),
                false,
                OutputInterface::OUTPUT_RAW
            );
        }

        parent::flush();
    }
}

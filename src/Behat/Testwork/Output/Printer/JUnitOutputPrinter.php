<?php

/*
 * This file is part of the Behat Testwork.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Testwork\Output\Printer;

use Behat\Testwork\Output\Exception\MissingExtensionException;
use Behat\Testwork\Output\Exception\MissingOutputPathException;
use Behat\Testwork\Output\Printer\Factory\FilesystemOutputFactory;
use DOMDocument;
use DOMElement;
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
    public const XML_VERSION = '1.0';
    public const XML_ENCODING = 'UTF-8';

    private ?DOMDocument $domDocument = null;

    private DOMElement $currentTestsuite;

    private DOMElement $currentTestcase;

    private DOMElement $testSuites;

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
    public function createNewFile($name, array $testsuitesAttributes = []): void
    {
        // This requires the DOM extension to be enabled.
        if (!extension_loaded('dom')) {
            throw new MissingExtensionException('The PHP DOM extension is required to generate JUnit reports.');
        }
        $this->setFileName(strtolower(trim((string) preg_replace('/[^[:alnum:]_]+/', '_', $name), '_')));

        $this->domDocument = new DOMDocument(self::XML_VERSION, self::XML_ENCODING);
        $this->domDocument->formatOutput = true;

        $this->testSuites = $this->domDocument->createElement('testsuites');
        $this->domDocument->appendChild($this->testSuites);
        $this->addAttributesToNode($this->testSuites, array_merge(['name' => $name], $testsuitesAttributes));
        $this->flush();
    }

    /**
     * Adds a new <testsuite> node.
     */
    public function addTestsuite(array $testsuiteAttributes = []): void
    {
        $this->currentTestsuite = $this->domDocument->createElement('testsuite');
        $this->testSuites->appendChild($this->currentTestsuite);
        $this->addAttributesToNode($this->currentTestsuite, $testsuiteAttributes);
    }

    /**
     * Extends the current <testsuite> node.
     *
     * @param array<string, string|int|null> $testsuiteAttributes
     */
    public function extendTestsuiteAttributes(array $testsuiteAttributes): void
    {
        $this->addAttributesToNode($this->currentTestsuite, $testsuiteAttributes);
    }

    /**
     * Adds a new <testcase> node.
     */
    public function addTestcase(array $testcaseAttributes = []): void
    {
        $this->currentTestcase = $this->domDocument->createElement('testcase');
        $this->currentTestsuite->appendChild($this->currentTestcase);
        $this->addAttributesToNode($this->currentTestcase, $testcaseAttributes);
    }

    /**
     * Adds attributes to the current <testcase> node.
     */
    public function addCurrentTestCaseAttributes(array $testcaseAttributes): void
    {
        $this->addAttributesToNode($this->currentTestcase, $testcaseAttributes);
    }

    /**
     * Add a testcase child element.
     *
     * @param string $nodeName
     * @param string $nodeValue
     */
    public function addTestcaseChild($nodeName, array $nodeAttributes = [], $nodeValue = null): void
    {
        $childNode = $this->domDocument->createElement($nodeName, $nodeValue ?? '');
        $this->currentTestcase->appendChild($childNode);
        $this->addAttributesToNode($childNode, $nodeAttributes);
    }

    private function addAttributesToNode(DOMElement $node, array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $node->setAttribute($name, $value ?? '');
        }
    }

    /**
     * Sets file name.
     *
     * @param string $fileName
     * @param string $extension The file extension, defaults to "xml"
     */
    public function setFileName($fileName, $extension = 'xml'): void
    {
        if ('.' . $extension !== substr($fileName, strlen($extension) + 1)) {
            $fileName .= '.' . $extension;
        }
        $outputFactory = $this->getOutputFactory();
        assert($outputFactory instanceof FilesystemOutputFactory);
        $outputFactory->setFileName($fileName);
        $this->flush();
    }

    /**
     * Generate XML from the DOMDocument and send to the writing stream.
     */
    public function flush(): void
    {
        if ($this->domDocument instanceof DOMDocument) {
            try {
                $this->getWritingStream()->write(
                    $this->domDocument->saveXML(null, LIBXML_NOEMPTYTAG),
                    false,
                    OutputInterface::OUTPUT_RAW
                );
            } catch (MissingOutputPathException) {
                throw new MissingOutputPathException(
                    'The `output_path` option must be specified for the junit formatter.',
                );
            }
        }

        parent::flush();
    }
}

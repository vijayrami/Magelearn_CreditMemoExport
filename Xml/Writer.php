<?php

namespace Magelearn\CreditMemoExport\Xml;

use DOMDocument;
use DOMNode;
use Magento\Framework\Exception\LocalizedException;

class Writer
{
    private const DEFAULT_ITEM_NAME = 'item';

    /**
     * @var string
     */
    private $rootName;

    /**
     * @var string[]
     */
    private $arrayItemMap;

    /**
     * @param string $rootName
     * @param string[] $arrayItemMap key->value pairs of array item names e.g. ['people' => 'person']
     */
    public function __construct(string $rootName = 'root', $arrayItemMap = [])
    {
        $this->rootName = $rootName;
        $this->arrayItemMap = $arrayItemMap;
    }

    /**
     * @param mixed[] $data
     * @return DOMDocument
     * @throws LocalizedException if unable to format data as XML.
     */
    public function toXml(array $data): DOMDocument
    {
        $dataRoot = $this->extractRootFromData($data);

        if (!$dataRoot) {
            return $this->toXml([$this->rootName => $data]);
        }

        $data = $data[$dataRoot];

        $document = new DOMDocument('1.0', 'UTF-8');
        $document->appendChild($this->toDomNode($document, $dataRoot, $data));

        // we want it to be pretty
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    /**
     * @param mixed[] $data
     */
    private function extractRootFromData(array $data): ?string
    {
        if (count($data) !== 1) {
            return null;
        }

        $key = key($data);

        return is_string($key) && !is_numeric($key) ? $key : null;
    }

    private function toDomNode(DOMDocument $document, $name, $data): DOMNode
    {
        $node = $document->createElement($name);

        if ($data === null) {
            return $node;
        }

        if (is_scalar($data)) {
            $node->appendChild($this->createTextNode($document, $data));

            return $node;
        }

        foreach ($data as $key => $value) {
            if ($key !== '_attributes') {
                $nodeName = is_numeric($key) ? $this->getItemName($name) : $key;
                $node->appendChild($this->toDomNode($document, $nodeName, $value));
                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            foreach ($value as $attributeName => $attributeValue) {
                $node->setAttribute($attributeName, $attributeValue);
            }
        }

        return $node;
    }

    private function getItemName($parentNodeName): string
    {
        return array_key_exists($parentNodeName, $this->arrayItemMap)
            ? $this->arrayItemMap[$parentNodeName]
            : self::DEFAULT_ITEM_NAME;
    }

    /**
     * @throws LocalizedException if unable to create text node.
     */
    private function createTextNode(DOMDocument $document, $data): DOMNode
    {
        $type = gettype($data);
        switch ($type) {
            case 'boolean':
                $value = $data ? 1 : 0;
                $cdata = false;
                break;
            case 'integer':
                $value = (string) $data;
                $cdata = false;
                break;
            case 'double':
                $value = number_format($data, 4, '.', '');
                $cdata = false;
                break;
            case 'string':
                $value = $data;
                $cdata = $this->requiresCdata($value);
                break;
            default:
                throw new LocalizedException(
                    __('Data is not primitive: expected bool, int, double or string. %type given.', $type)
                );
        }

        return $cdata
            ? $document->createCDATASection($value)
            : $document->createTextNode($value);
    }

    private function requiresCdata($value): bool
    {
        $chars = ['@', ' '];

        foreach ($chars as $char) {
            if (strpos($value, $char) !== false) {
                return true;
            }
        }

        $filterUrl = filter_var($value, FILTER_VALIDATE_URL);

        return $filterUrl !== false;
    }
}

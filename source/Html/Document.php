<?php

namespace ic\Framework\Html;

use ic\Framework\Support\Str;
use ic\Framework\Support\Text;

/**
 * Class Document
 *
 * @package ic\Framework\Dom
 */
class Document extends \DOMDocument
{

    /**
     * Root ID
     */
    const ROOT = 'document-parser-root';

    /**
     * @var string|null
     */
    public $language;

    /**
     * @var \DOMXPath|null
     */
    private $xpath;

    /**
     * @var array
     */
    private static $disallowedAttributes = [
        'frameborder',
        'border',
        'cellspacing',
        'cellpadding',
    ];

    /**
     * Document constructor.
     *
     * @param string|null $language
     */
    public function __construct($language = null)
    {
        parent::__construct('1.0', 'UTF-8');

        $this->language = $language;

        $this->registerNodeClass(\DOMElement::class, Element::class);
    }

    private function getFlags()
    {
        return LIBXML_NOBLANKS | LIBXML_NOXMLDECL | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED;
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    public function loadMarkup($source)
    {
        $this->preserveWhiteSpace = false;
        $this->substituteEntities = false;
        $this->encoding           = Str::getEncoding();

        $source = $this->addRootNode($source);

        if (@$this->loadHTML(Str::toEntities($source), $this->getFlags())) {
            $this->formatOutput = false;
            $this->xpath        = null;

            return true;
        }

        return false;
    }

    /**
     * @param \DOMNode $node
     *
     * @return string
     */
    public function saveMarkup(\DOMNode $node = null)
    {
        $this->normalizeDocument();

        if ($node === null) {
            $this->removeRootNode();
        }

        return Str::fromEntities($this->saveHTML($node));
    }

    /**
     * @param \ic\Framework\Support\Text $text
     *
     * @return bool
     */
    public function loadText(Text $text)
    {
        return $this->loadMarkup($text->whitespace()->toString());
    }

    /**
     * @param \DOMNode|null $node
     *
     * @return Text
     */
    public function saveText(\DOMNode $node = null)
    {
        return new Text($this->saveMarkup($node));
    }

    /**
     * Adds a root node just in case the source does not have one.
     *
     * @param string $source
     *
     * @return string
     */
    protected function addRootNode($source)
    {
        return Tag::div(['id' => self::ROOT], $source)->render();
    }

    /**
     * Removes the root node.
     */
    protected function removeRootNode()
    {
        $root = $this->query(sprintf('//*[@id="%s"]', self::ROOT));
        $this->removeElements($root);
    }

    /**
     * Evaluates the given XPath expression.
     *
     * @param string        $query XPath expression
     * @param null|\DOMNode $context
     *
     * @return \DOMNodeList
     */
    public function query($query, $context = null)
    {
        if ($this->xpath === null) {
            $this->xpath = new \DOMXPath($this);
        }

        return $this->xpath->query($query, $context);
    }

    /**
     * @param string $tagName
     *
     * @return \DOMNodeList
     */
    public function getElementsWithAttributes($tagName = '*')
    {
        return $this->query(sprintf('//%s[@*]', $tagName));
    }

    /**
     * @param string $tagName
     *
     * @return \DOMNodeList
     */
    public function getElementsWithoutAttributes($tagName = '*')
    {
        return $this->query(sprintf('//%s[not(@*)]', $tagName));
    }

    /**
     * Gets elements by class name.
     *
     * @param string $className
     *
     * @return \DOMNodeList
     */
    public function getElementsByClassName($className)
    {
        return $this->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
    }

    /**
     * @return \DOMNodeList
     */
    public function getComments()
    {
        return $this->query('//comment()');
    }

    /**
     * @param \DOMNodeList $nodes
     * @param array        $disallowedAttributes
     * @param array        $allowedStyles
     *
     * @return static
     */
    public function cleanAttributes(\DOMNodeList $nodes, array $disallowedAttributes = [], array $allowedStyles = [])
    {
        $disallowedAttributes = array_merge($disallowedAttributes, static::$disallowedAttributes);

        /** @var $node Element */
        foreach ($nodes as $node) {
            $node->cleanAttributes($disallowedAttributes, $allowedStyles);
        }

        return $this;
    }

    /**
     * @param array $disallowedAttributes
     * @param array $allowedStyles
     *
     * @return static
     */
    public function cleanDocumentAttributes(array $disallowedAttributes = [], array $allowedStyles = [])
    {
        return $this->cleanAttributes($this->getElementsWithAttributes(), $disallowedAttributes, $allowedStyles);
    }

    /**
     * @return $this
     */
    public function cleanDocument()
    {
        $this->removeElements($this->getElementsWithoutAttributes('span'));
        $this->removeEmptyTextNodes();

        return $this;
    }

    /**
     * Removes all the comments.
     *
     * @return static
     */
    public function removeComments()
    {
        /** @var $node \DOMElement */
        foreach ($this->getComments() as $node) {
            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    /**
     * Removes elements, but not their children.
     *
     * @param \DOMNodeList $nodes
     *
     * @return static
     */
    public function removeElements(\DOMNodeList $nodes)
    {
        /** @var Element $node */
        foreach ($nodes as $node) {
            if ($node->hasChildNodes()) {
                $fragment = $this->createDocumentFragment();

                while ($node->firstChild) {
                    $fragment->appendChild($node->firstChild);
                }

                $node->parentNode->replaceChild($fragment, $node);
            } else {
                $node->parentNode->removeChild($node);
            }
        }

        return $this;
    }

    /**
     * @param \DOMNodeList $nodes
     * @param string       $tagName
     *
     * @return static
     */
    public function renameElements(\DOMNodeList $nodes, $tagName)
    {
        /** @var Element $node */
        foreach ($nodes as $node) {
            $node->setTagName($tagName);
        }

        return $this;
    }

    /**
     * Removes empty text nodes.
     */
    protected function removeEmptyTextNodes()
    {
        while (($nodes = $this->query('//*[not(*) and not(@*) and not(text()[normalize-space()]) and not(self::br)]')) && $nodes->length) {
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->saveMarkup();
    }

}
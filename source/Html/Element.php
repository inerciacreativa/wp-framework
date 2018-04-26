<?php

namespace ic\Framework\Html;

/**
 * Class Element
 *
 * @package ic\Framework\Html
 *
 * @property Document ownerDocument
 */
class Element extends \DOMElement
{

    /**
     * @param $tagName
     * @return \DOMElement
     */
    public function setTagName($tagName)
    {
        if ($tagName === $this->tagName) {
            return $this;
        }

        $element = $this->ownerDocument->createElement($tagName);

        // Copy attributes
        foreach ($this->attributes as $attribute) {
            $element->setAttribute($attribute->nodeName, $attribute->nodeValue);
        }

        // Copy children nodes
        while ($this->firstChild) {
            $element->appendChild($this->firstChild);
        }

        $this->parentNode->replaceChild($element, $this);

        return $element;
    }

    /**
     * Returns the content of the class attribute as an array.
     *
     * @return array
     */
    public function getClassNames()
    {
        $classes = $this->hasAttribute('class') ? explode(' ', $this->getAttribute('class')) : [];
        $classes = array_filter($classes);

        return $classes;
    }

    /**
     * Adds a tagName to the class attribute.
     *
     * @param string $className
     * @return bool
     */
    public function addClassName($className)
    {
        $classes = $this->getClassNames();

        if (!in_array($className, $classes, false)) {
            $classes[] = $className;
            $this->setAttribute('class', implode(' ', $classes));

            return true;
        }

        return false;
    }

    /**
     * Removes a tagName from the class attribute.
     *
     * @param string $className
     * @return bool
     */
    public function removeClassName($className)
    {
        $classes = $this->getClassNames();

        if (($key = array_search($className, $classes, false)) !== false) {
            unset($classes[$key]);
            $this->setAttribute('class', implode(' ', $classes));

            return true;
        }

        return false;
    }

    /**
     * Clean the attributes.
     *
     * @param array $disallowedAttributes
     * @param array $allowedStyles
     */
    public function cleanAttributes(array $disallowedAttributes = [], array $allowedStyles = [])
    {
        $remove = [];

        /** @var \DOMAttr $attribute */
        foreach ($this->attributes as $attribute) {
            if (in_array($attribute->nodeName, $disallowedAttributes, false)) {
                $remove[] = $attribute->nodeName;
            } elseif ($attribute->nodeName === 'style') {
                $styles = [];

                if (!empty($allowedStyles)) {
                    $styles = $this->cleanStyles($attribute->nodeValue, $allowedStyles);
                }

                if (empty($styles)) {
                    $remove[] = 'style';
                } else {
                    $this->setAttribute('style', implode(';', $styles));
                }
            } elseif ($attribute->nodeName === 'align') {
                $className = strtolower('align' . $attribute->nodeValue);

                if ($className !== 'alignjustify') {
                    $this->addClassName($className);
                }

                $remove[] = 'align';
            } elseif ($attribute->nodeName === 'lang' && $attribute->nodeValue === $this->ownerDocument->language) {
                $remove[] = 'lang';
            }
        }

        foreach ($remove as $attribute) {
            $this->removeAttribute($attribute);
        }
    }

    /**
     * Removes all style declarations not allowed.
     *
     * @param string $value
     * @param array  $allowed
     * @return array
     */
    private function cleanStyles($value, $allowed)
    {
        $styles = explode(';', $value);
        $styles = array_map('trim', $styles);
        $styles = array_filter($styles);
        $result = [];

        foreach ($styles as $style) {
            $style = explode(':', strtolower($style));
            $style = array_map('trim', $style);

            if (in_array($style[0], $allowed, false)) {
                $result[] = sprintf('%s: %s', $style[0], $style[1]);
            }
        }

        return $result;
    }

}
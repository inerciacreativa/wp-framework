<?php

namespace ic\Framework\Dom;

use DOMAttr;
use DOMElement;

/**
 * Class Element
 *
 * @package ic\Framework\Dom
 *
 * @property Document ownerDocument
 */
class Element extends DOMElement
{

	/**
	 * @param string $tagName
	 *
	 * @return static
	 */
	public function setTagName(string $tagName): Element
	{
		if ($tagName === $this->tagName) {
			return $this;
		}

		/** @var Element $element */
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
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getAttribute($name, $default = '')
	{
		$result = parent::getAttribute($name);
		if ($result === '') {
			return $default;
		}

		if (!is_string($default)) {
			settype($result, gettype($default));
		}

		return $result;
	}

	/**
	 * Returns the content of the class attribute as an array.
	 *
	 * @return array
	 */
	public function getClassNames(): array
	{
		if (!$this->hasAttribute('class')) {
			return [];
		}

		$classes = explode(' ', $this->getAttribute('class'));

		return array_filter($classes);
	}

	/**
	 * Adds a className to the class attribute.
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public function addClassName(string $className): bool
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
	 * Removes a className from the class attribute.
	 *
	 * @param string $className
	 *
	 * @return bool
	 */
	public function removeClassName(string $className): bool
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
	public function cleanAttributes(array $disallowedAttributes = [], array $allowedStyles = []): void
	{
		$remove = [];

		/** @var DOMAttr $attribute */
		foreach ($this->attributes as $attribute) {
			if (in_array($attribute->nodeName, $disallowedAttributes, false)) {
				$remove[] = $attribute;
			} elseif ($attribute->nodeName === 'style') {
				$styles = $this->filterStyles($attribute->nodeValue, $allowedStyles);

				if (empty($styles)) {
					$remove[] = $attribute;
				} else {
					$this->setAttribute('style', implode(';', $styles));
				}
			} elseif ($attribute->nodeName === 'align') {
				$className = strtolower('align' . $attribute->nodeValue);

				if ($className !== 'alignjustify') {
					$this->addClassName($className);
				}

				$remove[] = $attribute;
			} elseif ($attribute->nodeName === 'lang' && $attribute->nodeValue === $this->ownerDocument->language) {
				$remove[] = $attribute;
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
	 *
	 * @return array
	 */
	protected function filterStyles(string $value, array $allowed): array
	{
		$result = [];

		if (empty($allowed)) {
			return $result;
		}

		$styles = explode(';', $value);
		$styles = array_map('trim', $styles);
		$styles = array_filter($styles);

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

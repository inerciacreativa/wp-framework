<?php

namespace ic\Framework\Plugin;

/**
 * Trait MetadataDecorator
 *
 * @package ic\Framework\Plugin
 */
trait MetadataDecorator
{

	/**
	 * @var Metadata
	 */
	private $metadata;

	/**
	 * @param string|Metadata $source
	 *
	 * @return $this
	 *
	 * @throws \RuntimeException
	 */
	protected function setMetadata($source): self
	{
		$this->metadata = $source instanceof Metadata ? $source : new Metadata($source);

		return $this;
	}

	/**
	 * @param string $metadata
	 *
	 * @return string|Metadata
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	public function getMetadata(string $metadata = null)
	{
		if ($this->metadata === null) {
			throw new \RuntimeException(sprintf('There is no attached metadata in "%s".', static::class));
		}

		if (empty($metadata)) {
			return $this->metadata;
		}

		if (!isset($this->metadata->$metadata)) {
			throw new \InvalidArgumentException(sprintf('There is no attached metadata "%s" in "%s".', $metadata, static::class));
		}

		return $this->metadata->$metadata;
	}

	/**
	 * @return string
	 */
	public function id(): string
	{
		return $this->getMetadata('id');
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->getMetadata('name');
	}

	/**
	 * @return string
	 */
	public function version(): string
	{
		return $this->getMetadata('version');
	}

	/**
	 * @return string
	 */
	public function languages(): string
	{
		return $this->getMetadata('languages');
	}

}

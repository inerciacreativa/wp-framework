<?php

namespace ic\Framework\Support;

use ic\Framework\Support\Limiter\LimiterResolverInterface;
use ic\Framework\Support\Limiter\LettersResolver;
use ic\Framework\Support\Limiter\WordsResolver;
use ic\Framework\Html\Document;

/**
 * Class TextLimiter
 *
 * @package ic\Framework\Support
 */
class TextLimiter
{

    /**
     * @var LimiterResolverInterface
     */
    protected $resolver;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var bool
     */
    protected $reached = false;

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var array
     */
    private $nodes = [];

    /**
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    public static function words($string, $limit)
    {
        $limiter = new static(new WordsResolver());

        return $limiter->limit($string, (int)$limit);
    }

    /**
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    public static function letters($string, $limit)
    {
        $limiter = new static(new LettersResolver());

        return $limiter->limit($string, (int)$limit);
    }

    /**
     * StrLimit constructor.
     *
     * @param LimiterResolverInterface $resolver
     */
    protected function __construct(LimiterResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param string $string
     * @param int    $limit
     *
     * @return string
     */
    protected function limit($string, $limit)
    {
        $dom = new Document();
        $dom->loadMarkup(Str::toEntities($string));

        $this->walk($dom, $limit);

        foreach ($this->nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        $this->nodes = [];

        return $dom->saveMarkup();
    }

    /**
     * @param \DOMNode $node
     * @param int      $limit
     *
     * @return array
     */
    protected function walk(\DOMNode $node, $limit)
    {
        if ($this->count >= $limit) {
            $this->nodes[] = $node;
        } else {
            if ($node instanceof \DOMText) {
                $count = $this->resolver->count($node->nodeValue);

                if (($this->count + $count) > $limit) {
                    $node->nodeValue = $this->resolver->limit($node->nodeValue, $limit - $this->count);

                    $this->count = $limit;
                } else {
                    $this->count += $count;
                }

            }

            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $child) {
                    $this->walk($child, $limit);
                }
            }
        }
    }

}
<?php

namespace ic\Framework\Support\Limiter;

/**
 * Class WordsResolver
 *
 * @package ic\Framework\Support\Limiter
 */
class WordsResolver implements LimiterResolverInterface
{

    /**
     * @inheritdoc
     */
    public function count($string)
    {
        return count(preg_split('/(\s+)/', $string, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * @inheritdoc
     */
    public function limit($string, $number)
    {
        $result = '';
        $count  = 0;
        $words  = preg_split('/(\s+)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($words as $word) {
            if (trim($word) === '') {
                $count++;
            }

            if ($count >= $number) {
                break;
            }

            $result .= $word;
        }

        return $result;
    }

}
<?php

namespace Medboubazine\NumberFormatter;

use Medboubazine\NumberFormatter\Parse\NumberFormatParse;

class NumberFormat
{

    /**
     * number
     *
     * @var float
     */
    protected static $number;

    /**
     * __toString
     *
     * @return float
     */
    public function __toString()
    {
        return self::$number;
    }
    /**
     * parse
     *
     * @param  float $number_
     * @return object|float
     */
    public static function parse($number_, $decimals = null)
    {
        self::$number = $number_;
        $parser = new NumberFormatParse($number_);
        if (is_int($decimals) and  $decimals > 0) {
            return $parser->get($decimals);
        }
        return $parser;
    }
}

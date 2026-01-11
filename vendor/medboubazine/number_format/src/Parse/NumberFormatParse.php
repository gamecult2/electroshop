<?php

namespace Medboubazine\NumberFormatter\Parse;

class NumberFormatParse
{

    /**
     * __construct
     *
     * @param  float $number
     * @return void
     */
    public function __construct($number)
    {
        $this->number = $number;
    }
    /**
     * __toString
     *
     * @return float
     */
    public function __toString()
    {
        return $this->get(2);
    }
    /**
     * number
     *
     * @var int
     */
    protected $number = 0;
    /**
     * get
     *
     * @param  float $decimals
     * @return void
     */
    public function get(int $decimals = 2)
    {
        return ($decimals) ? $this->number_format($this->number, $decimals) : (float) $this->number;
    }
    /**
     * number_format
     *
     * @param  float $number
     * @param  int $decimals
     * @param  string $dec_point
     * @param  string $thousands_sep
     * @return float
     */
    protected function number_format($number, int $decimals = 0, string $dec_point = ".", string $thousands_sep = "")
    {
        return number_format($number, $decimals, $dec_point, $thousands_sep);
    }
}

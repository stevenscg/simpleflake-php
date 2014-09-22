<?php
/**
 * SimpleFlake - Simple distributed ID generation
 * https://github.com/stevenscg/simpleflake-php
 *
 * Based on the original Python implementation "simpleflake"
 * @see https://github.com/SawdustSoftware/simpleflake
 *
 * @author Chris Stevens
 */
class SimpleFlake
{
    /**
     * Epoch for simpleflake timestamps, starts at the year 2000
     */
    protected $epoch = 946702800;

    /**
     * Field lengths in bits
     */
    protected $timestamp_length = 41;
    protected $random_length = 23;

    /**
     * Left shift amounts
     */
    protected $random_shift = 0;
    protected $timestamp_shift = 23;

    /**
     * Internal storage
     */
    protected $flake = null;


    /**
     * Generate a 64 bit, roughly-ordered, globally-unique ID 
     *
     * @param integer $timestamp
     * @param integer $random_bits
     * @param integer $epoch
     */
    public function __construct($timestamp = null, $random_bits = null, $epoch = null)
    {
        $this->flake = $this->simpleflake($timestamp, $random_bits, $epoch);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ( ! $this->flake ){
            $this->flake = $this->simpleflake();
        }
        return number_format($this->flake, 0, '', '');
    }

    /**
     * Generate a 64 bit, roughly-ordered, globally-unique ID
     *
     * @param integer $timestamp
     * @param integer $random_bits
     * @param integer $epoch
     * @return integer
     */
    public function simpleflake($timestamp = null, $random_bits = null, $epoch = null)
    {
        // timestamp in seconds (float)
        if (is_null($timestamp)) {
            $timestamp = microtime(true);
        }
        if (is_null($epoch)) {
            $epoch = $this->epoch;
        }

        $second_time = $timestamp;
        $second_time -= $epoch;
        $millisecond_time = (int) ($second_time * 1000);

        if (is_null($random_bits)) {
            $bits_as_hex = $this->random_bits(23);
            $random_bits = hexdec($bits_as_hex);
        }

        $flake = ($millisecond_time << $this->timestamp_shift) | $random_bits;

        return $flake;
    }


    /**
     * Parses a simpleflake and returns a named tuple with the parts
     *
     * @param $flake
     * @return array
     */
    public function parse_simpleflake($flake)
    {
        $timestamp = $this->epoch | $this->extract_bits(
            $flake,
            $this->timestamp_shift,
            $this->timestamp_length
        ) / 1000.0;

        $random  = $this->extract_bits(
            $flake,
            $this->timestamp_shift,
            $this->timestamp_length
        ) / 1000.0;

        return array($timestamp, $random);
    }


    /**
     * Counts how many bits are needed to represent $value
     * @see http://stackoverflow.com/a/5302533/538353
     *
     * @param $value
     * @return int
     */
    private function count_bits($value)
    {
        for ($count = 0; $value != 0; $value >>= 1) {
            ++$count;
        }
        return $count;
    }


    /**
     * Returns a base16 random string of at least $bits bits
     * Actual bits returned will be a multiple of 4 (1 hex digit)
     * @see http://stackoverflow.com/a/5302533/538353
     *
     * @param $bits
     * @return string
     */
    private function random_bits($bits)
    {
        $result = '';
        $accumulated_bits = 0;
        $total_bits = $this->count_bits(mt_getrandmax());
        $usable_bits = intval($total_bits / 8) * 8;

        while ($accumulated_bits < $bits) {
            $bits_to_add = min($total_bits - $usable_bits, $bits - $accumulated_bits);
            if ($bits_to_add % 4 != 0) {
                // add bits in whole increments of 4
                $bits_to_add += 4 - $bits_to_add % 4;
            }

            // isolate leftmost $bits_to_add from mt_rand() result
            $more_bits = mt_rand() & ((1 << $bits_to_add) - 1);

            // format as hex (this will be safe)
            $format_string = '%0'.($bits_to_add / 4).'x';
            $result .= sprintf($format_string, $more_bits);
            $accumulated_bits += $bits_to_add;
        }
        return $result;
    }


    /**
     * Extract a portion of a bit string. Similar to substr()
     *
     * @param $data
     * @param $shift
     * @param $length
     * @return int
     */
    private function extract_bits($data, $shift, $length)
    {
        $bitmask = ((1 << $length) - 1) << $shift;
        return (($data & $bitmask) >> $shift);
    }
}

<?php

namespace ierusalim\Random;

/**
 * This class contains RandomGen
 *
 * PHP Version 5.6
 * 
 * @package   ierusalim\RandomGen
 * @author    Alexander Jer <alex@ierusalim.com>
 * @copyright 2017, Ierusalim
 * @license   https://opensource.org/licenses/Apache-2.0 Apache-2.0
 */
class RandomGen
{

    /**
     * Random string generate (specified length and optional characters-list)
     * 
     * The available characters can be set by parameter string of $chars_init
     * May to set the parameters once, the next time they will be used as stored
     * 
     * @param integer $len Length of generate string
     * @param string  $chars_init Characters available for use
     *
     * @api
     * @staticvar string $chars
     * 
     * @return string
     */
    public function genRandomStr($len = 10, $chars_init = false)
    {
        static $chars
          = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890_=';
        if ($chars_init) {
            $chars = $chars_init;
        }
        $l = \strlen($chars);
        $outstr = '';
        foreach (\unpack('v*', openssl_random_pseudo_bytes($len * 2)) as $n) {
            $outstr .= $chars[$n % $l];
        }
        return $outstr;
    }

    /**
     * Random Array generate (keys=random strings, values=random strings or arrays)
     * 
     * @param integer $min_elem Min.count of elements in the array being generated
     * @param integer $max_elem Max.count of elements in the array being generated
     * @param integer $max_depth   The maximum depth of nesting arrays
     * @param integer $threshold   Chance array or string generation (0-65535)
     * @param integer $min_key_len Min.chars count for array-keys
     * @param integer $max_key_len Max.chars count for array-keys
     * @param integer $min_val_len Min.chars count for string-values
     * @param integer $max_val_len Min.chars count for string-values
     * 
     * @return array
     */
    public function genRandomStrArrStrKeys(
        $min_elem = 3,
        $max_elem = 10,
        $max_depth = 3,
        $threshold = 32768,
        $min_key_len = 1,
        $max_key_len = 10,
        $min_val_len = 1,
        $max_val_len = 10
    )
    {
        if ($min_key_len < 0
            || $max_key_len < $min_key_len
            || $min_val_len < 0
            || $max_val_len < $min_val_len 
        ) {
            return false;
        }

        $val_len = rand($min_val_len, $max_val_len);

        if ($max_depth<1) {
            return self::genRandomStr($val_len);
        }

        $elem_cnt = rand($min_elem, $max_elem);
        $r_arr = [];

        foreach (
            \unpack('v*', \openssl_random_pseudo_bytes($elem_cnt * 2)) as $v
        ) {
            $r_arr[self::genRandomStr(rand($min_key_len, $max_key_len))] 
            = ($v > $threshold) ?
            self::genRandomStr($val_len) :
            self::genRandomStrArrStrKeys(
                $min_elem,
                $max_elem,
                $max_depth - 1,
                $threshold,
                $min_key_len,
                $max_key_len,
                $min_val_len,
                $max_val_len
            );
        }
        return $r_arr;
    }
}

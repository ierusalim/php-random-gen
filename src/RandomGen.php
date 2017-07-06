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
    protected $char_sets;
    
    public function __construct($init_charset = NULL)
    {
        if(is_array($init_charset)) {
            $this->char_sets = $init_charset;
        } elseif(is_string($init_charset)) {
            $this->char_sets = [$init_charset];
        } else {
            $this->char_sets = [
                'abcdefghijklmnopqrstuvwxyz'.
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                '01234567890_-.'
                ];
        }
    }

    /**
     * Random string generate (specified length and optional characters-list)
     * 
     * The available characters can be set by parameter string of $char_set_init
     * May to set the parameters once, the next time they will be used as stored
     * 
     * @param integer $len Length of generate string
     * @param integer $char_set_num Number of char_set selected for use
     *
     * @api
     * @staticvar string $chars
     * 
     * @return string
     */
    public function genRandomStr($len = 10, $char_set_num=0)
    {
        $l = \strlen($this->char_sets[$char_set_num]);
        $outstr = '';
        foreach (\unpack('v*', openssl_random_pseudo_bytes($len * 2)) as $n) {
            $outstr .= $this->char_sets[$char_set_num][$n % $l];
        }
        return $outstr;
    }

    /**
     * Counting all elements in array (any depth of nesting)
     * 
     * @param array   $arr
     * @param integer $cnt
     * 
     * @return integer
     */
    public function countArrayElemetsRecursive(&$arr, $cnt=0) {
        array_walk_recursive($arr, function($k, $v) use (&$cnt) { $cnt++; });
        return $cnt;
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
            return $this->genRandomStr($val_len);
        }

        $elem_cnt = rand($min_elem, $max_elem);
        $r_arr = [];

        foreach (
            \unpack('v*', \openssl_random_pseudo_bytes($elem_cnt * 2)) as $v
        ) {
            $r_arr[$this->genRandomStr(rand($min_key_len, $max_key_len))] 
            = ($v > $threshold) ?
            $this->genRandomStr($val_len) :
            $this->genRandomStrArrStrKeys(
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
    
    public function genRandomStrArrNumKeys(
        $min_elem = 3,
        $max_elem = 10,
        $max_depth = 3,
        $threshold = 32768,
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
            return $this->genRandomStr($val_len);
        }

        $elem_cnt = rand($min_elem, $max_elem);
        $r_arr = [];

        foreach (
            \unpack('v*', \openssl_random_pseudo_bytes($elem_cnt * 2)) as $k=>$v
        ) {
            $r_arr[$k] = ($v > $threshold) ?
            $this->genRandomStr($val_len) :
            $this->genRandomStrArrNumKeys(
                $min_elem,
                $max_elem,
                $max_depth - 1,
                $threshold,
                $min_val_len,
                $max_val_len
            );
        }
        return $r_arr;
    }
 
    public function genRandomNumArrNumKeys(
        $min_elem = 3,
        $max_elem = 10,
        $max_depth = 3,
        $threshold = 32768,
        $min_value_num=0,
        $max_value_num=65535
    ) {
        if ($min_key_len < 0
            || $max_key_len < $min_key_len
            || $min_val_len < 0
            || $max_val_len < $min_val_len 
        ) {
            return false;
        }

        if ($max_depth<1) {
            return rand($min_value_num, $max_value_num);
        }

        $elem_cnt = rand($min_elem, $max_elem);
        $r_arr = [];

        foreach (
            \unpack('v*', \openssl_random_pseudo_bytes($elem_cnt * 2)) as $k=>$v
        ) {
            $r_arr[$k] = ($v > $threshold) ?
            rand($min_value_num, $max_value_num) :
            $this->genRandomNumArrNumKeys(
                $min_elem,
                $max_elem,
                $max_depth - 1,
                $threshold
            );
        }
        return $r_arr;
    }

}

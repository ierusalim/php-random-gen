<?php

namespace ierusalim\Random;

/**
 * This class coniains RandomArray
 *
 * PHP Version 5.6
 * 
 * @package    ierusalim\RandomArray
 * @author     Alexander Jer <alex@ierusalim.com>
 * @copyright  2017, Ierusalim
 * @license    https://opensource.org/licenses/Apache-2.0 Apache-2.0
 */
class RandomArray Extends RandomStr
{
     /**
     * For generation random Arrays
     * if 0 then array keys will be simple 0,1,2... numeric
     * if 1 then array keys will be numeric from min_arr_key to max_arr_key
     * if 2 then array keys will be string len from min_arr_key to max_arr_key 
     * 
     * @var integer
     */
    protected $change_keys_model;
    /**
     * For generation random Arrays
     * minimal number for random numbers array-keys generation
     * or minimal string length for random string array-keys generation
     *
     * @var integer 
     */
    protected $min_arr_key;
    /**
     * For generation random Arrays
     * maximal number for random numbers array-keys generation
     * or maximal string length for random string array-keys generation
     *
     * @var integer 
     */
    protected $max_arr_key;

    /**
     * Counting all elements in array (any depth of nesting)
     * 
     * @param array   $arr
     * @param integer $cnt
     * 
     * @return integer
     */
    public function countArrayValuesRecursive(&$arr, $cnt=0) {
        array_walk_recursive($arr, function($k, $v) use (&$cnt) { $cnt++; });
        return $cnt;
    }
    
    /**
     * Set model for generation keys for random arrays
     * 
     * @param integer      $min
     * @param integer      $max
     * @param string|null  $chars
     */
    public function setKeysModel($min=0, $max=32767, $chars = null) 
    {
        if(empty($chars)) {
            //Numeric keys model
            $this->change_keys_model = 1;
            if(!$min) {
                //Non-changes key model (numeric 0,1,2...)
                $this->change_keys_model = 0;
            }
        } else {
            //Random-string keys model
            $this->change_keys_model = 2;
            //set $chars as charset number 1
            $this->char_sets[1] = $chars;
        }
        $this->min_arr_key = $min;
        $this->max_arr_key = $max;
    }
    
    public function setValuesModel($min=-32767, $max=32767, $chars = null)
    {
        if(empty($chars)) {
            //Numeric values model
            $this->change_values_model = 1;
            if($min == -32767) {
                //Non-changes values model (random number from -32767 to 32767)
                $this->change_values_model=0;
            }
        } else {
            //Random-string values model
            $this->change_values_model = 2;
            //set $chars as charset number 2
            $this->char_sets[2] = $chars;
        }
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
    public function genRandomArray(
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

        $elem_cnt = mt_rand($min_elem, $max_elem);
        $r_arr = [];

        foreach (
            \unpack('v*', call_user_func($this->rnd_fn, $elem_cnt * 2)) as $v
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
}

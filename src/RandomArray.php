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
 *
 * Examples of use:
 *  Initialize:
 *    $g = new RandomArray();
 * 
 *  //Generate random array with default parameters:
 *    $arr = $g->genRandomArray();
 *    print_r($arr);
 *
 *  //Generate random array with string keys from listed chars, 3-9 chars length
 *    $g->setKeysModel(3,9,'abcdefghijklmnopqrstuvwxyz');
 *    $g->setValuesModel(0,100); //random numeric values range from 0 to 100
 *    $arr = $g->genRandomArray(10,15,0); //generate 10-15 elements (not nested)
 *    print_r($arr);
 *
 */
class RandomArray extends RandomStr
{
    /**
     * Set this limit to avoid generation unexpected too large arrays
     *
     * @var integer
     */
    public $lim_elements = 10000;
    
     /**
     * Model number for generation random Keys for Arrays
     * if 0 then array keys will be simple 1,2,3... numeric
     * if 1 then array keys will be numeric from min_arr_key to max_arr_key
     * if 2 then array keys will be string len from min_arr_key to max_arr_key
     * This value setting by function setKeysModel()
     *
     * @var integer
     */
    protected $keys_model;

    /**
     * Model number for generation random Values for Arrays
     * if 0 then array values will be numeric from 0 to 65535
     * if 1 then array values will be numeric from min_arr_val to max_arr_val
     * if 2 then array values will be string len from min_arr_val to max_arr_val
     *  This value setting by function setValuesModel()
     *
     * @var integer
     */
    protected $values_model;
    
    /**
     * Value for generation random Keys for Arrays
     * This is minimal number for random numbers generation,
     * or minimal length of string for random string array-keys generation
     * This value setting by function setValuesModel()
     *
     * @var integer
     */
    protected $min_arr_key;

    /**
     * Value for generation random Keys for Arrays
     * This is maximal number for random numbers generation,
     * or maximal length of string for random string array-keys generation
     * This value setting by function setKeysModel()
     *
     * @var integer
     */
    protected $max_arr_key;

    /**
     * Value for generation random Values for Arrays
     * This is minimal number for random numbers generation,
     * or minimal length of string for random string array-values generation
     * This value setting by function setValuesModel()
     *
     * @var integer
     */
    protected $min_arr_val;
    
    /**
     * Value for generation random Values for Arrays
     * This is maximal number for random numbers generation,
     * or maximal length of string for random string array-values generation
     *
     * @var integer
     */
    protected $max_arr_val;
    
    /**
     * Value generate function
     * calling if values_model == 3
     * 
     * @var callable
     */
    public $gen_value_fn;
    
    /**
     * Key generate function
     * calling if keys_model == 3
     * 
     * @var callable
     */
    public $gen_key_fn;

    /**
     * Init parameter - string of chars for generation array values
     * Prefer to leave blank and use function setValuesModel for set it.
     *
     * @param string|null $init_val_charset
     */
    public function __construct($init_val_charset = null)
    {
        parent::__construct($init_val_charset);
        $this->setKeysModel();
        if (\is_null($init_val_charset)) {
            $this->setValuesModel(1, 16, $this->char_sets[0]);
        } else {
            $this->setValuesModel(1, 16, $init_val_charset);
        }
    }

    /**
     * Counting all values in array (recursive)
     *
     * @param array   $arr
     * @param integer $cnt
     *
     * @return integer
     */
    public function countArrayValuesRecursive(&$arr, $cnt = 0)
    {
        \array_walk_recursive($arr, function ($v, $k) use (&$cnt) {
            $cnt++;
        });
        return $cnt;
    }
    
    /**
     * Counting the maximum depth of nesting of arrays in an array (recursive)
     *
     * @param array $arr
     * @param integer $c_depth
     *
     * @return integer
     */
    public function countArrayMaxDepth($arr, $c_depth = 0)
    {
        if (!is_array($arr)) {
            return false;
        }
        $m_depth = $c_depth;
        \array_walk($arr, function ($v, $k) use ($c_depth, &$m_depth) {
            if (is_array($v)) {
                $new_depth = $this->countArrayMaxDepth($v, $c_depth+1);
                if ($new_depth > $m_depth) {
                    $m_depth = $new_depth;
                }
            }
        });
        return $m_depth;
    }
    
    /**
     * Set model for generation keys for random arrays
     *
     * @param integer      $min
     * @param integer      $max
     * @param string|null  $chars
     */
    public function setKeysModel($min = 1, $max = null, $chars = null)
    {
        if (empty($chars)) {
            //Numeric keys model
            $this->keys_model = 1;
            if ($min == 1 && (\is_null($max))) {
                //Non-changes key model (numeric 0,1,2...)
                $this->keys_model = 0;
            }
            if (\is_null($max)) {
                $max = 65535;
            }
        } else {
            //Random-string keys model
            $this->keys_model = 2;
            //set $chars as charset number 1
            $this->char_sets[1] = $chars;
        }
        $this->min_arr_key = $min;
        $this->max_arr_key = is_null($max) ? 16 : $max;
    }
    
    /**
     * Set model for generation values for random arrays
     * 
     * @param integer     $min
     * @param integer     $max
     * @param string|null $chars
     */
    public function setValuesModel($min = 0, $max = 65535, $chars = null)
    {
        if (empty($chars)) {
            //Numeric values model
            $this->values_model = 1;
            if (!$min && $max==65535) {
                //Non-changes values model (random number from 0 to 65535)
                $this->values_model=0;
            }
        } else {
            //Random-string values model
            $this->values_model = 2;
            //set $chars as charset number 2
            $this->char_sets[2] = $chars;
        }
        $this->min_arr_val = $min;
        $this->max_arr_val = $max;
    }
    
    /**
     * Set function for generate values for random array
     * 
     * @param callable $gen_fn
     */
    public function setValuesModelFn(callable $gen_fn)
    {
        $this->values_model = 3;
        $this->gen_value_fn = $gen_fn;
    }

    /**
     * Set function for generate keys for random array
     * 
     * @param callable $gen_fn
     */
    public function setKeysModelFn(callable $gen_fn)
    {
        $this->keys_model = 3;
        $this->gen_key_fn = $gen_fn;
    }

    /**
     * Random Array generate
     *
     * Parameters $min_elem_cnt and $max_elem_cnt define the minimum and
     *   maximum number of elements of generated arrays
     *
     * Parameter $theshold define chance of generating scalar or nested array
     * If 0 is specified, scalar will always be generated, never nested array
     * if 65535 specified, nested array will be generated until reach $lim_depth
     * if 32768 specified the probability of generating array or scalar is 50/50
     * Only scalar generate if the depth of array nesting reached $lim_depth
     *
     * No more than $lim_elements of elements will be generated total
     *
     * @param integer $min_elem_cnt Min.count of elements in array (and nested)
     * @param integer $max_elem_cnt Max.count of elements in array (and nested)
     * @param integer $threshold Chance array or string generation (0-65535)
     * @param integer $lim_depth Depth limit of nesting arrays
     * @param integer $lim_elements Limit number of generated elements
     *
     * @return array
     */
    public function genRandomArray(
        $min_elem_cnt = 3,
        $max_elem_cnt = 10,
        $threshold = 32768,
        $lim_depth = 3,
        $lim_elements = 10000
    ) {
        if ($lim_elements) {
            $this->lim_elements = $lim_elements;
        }
        if ($lim_depth<1
            || $this->lim_elements < 0
            || $this->max_arr_key < 0
            || $this->max_arr_key < $this->min_arr_key
            || $this->max_arr_val < 0
            || $this->max_arr_val < $this->min_arr_val
        ) {
            return false;
        }

        $elem_cnt = mt_rand($min_elem_cnt, $max_elem_cnt);
        if ($elem_cnt > $this->lim_elements) {
            $elem_cnt = $this->lim_elements;
        }
        $r_arr = [];
        $gen_arr = \unpack('v*', \call_user_func($this->rnd_fn, $elem_cnt*2));
        $this->lim_elements-=$elem_cnt;
        
        foreach ($gen_arr as $k => $v) {
            if ($v > $threshold || $lim_depth<2 || $this->lim_elements <2) {
                if ($this->values_model) {
                    if ($this->values_model == 3) {
                        $v = \call_user_func($this->gen_value_fn,
                            \compact('k', 'v', 'threshold','lim_depth') 
                        );
                    } else {
                        $v = mt_rand($this->min_arr_val, $this->max_arr_val);
                        if ($this->values_model == 2) {
                            $v = $this->genRandomStr($v, 2);
                        }
                    }
                } else {
                    if (!$this->keys_model) {
                        continue;
                    }
                }
            } else {
                $v = $this->genRandomArray(
                    $min_elem_cnt,
                    $max_elem_cnt,
                    $threshold,
                    $lim_depth - 1,
                    0
                );
            }
            if ($this->keys_model) {
                if ($this->keys_model === 3) {
                    $k = \call_user_func($this->gen_key_fn,
                        \compact('k', 'v', 'threshold','lim_depth') 
                        );
                } else {
                    $k = \mt_rand($this->min_arr_key, $this->max_arr_key);
                    if ($this->keys_model === 2) {
                        $k = $this->genRandomStr($k, 1);
                    }
                }
                $r_arr[$k] = $v;
            } else {
                $gen_arr[$k] = $v;
            }
        }
        
        if ($this->keys_model) {
            return $r_arr;
        } else {
            return $gen_arr;
        }
    }
}

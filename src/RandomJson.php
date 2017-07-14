<?php

namespace ierusalim\Random;

/**
 * This class RandomJson is intended for generating random json-files
 *
 * PHP Version 5.6
 *
 * @package    ierusalim\RandomJson
 * @author     Alexander Jer <alex@ierusalim.com>
 * @copyright  2017, Ierusalim
 * @license    https://opensource.org/licenses/Apache-2.0 Apache-2.0
 *
 * Example of use:
 * $g = new RandomJson();
 * $g->setKeysModel();
 * $g->setValuesModel();
 * $file_name = $g->genRandomJson(10, 10, 32768, 3, 100, 65535);
 * $json_raw = file_get_contents($file_name);
 * $arr = json_decode($json_raw,true);
 * print_r($arr);
 *
 * //Example for ASCII chars and UTF-8 multibyte values:
 * $g->setKeysModel(5,8,implode(range('a','z')));
 * $g->setValuesModel(1,10, "神會貓性少女 迪克和陰部", true);
 * $file_name = $g->genRandomJson(10, 10, 32768, 3, 100, 0);
 * $json_raw = file_get_contents($file_name);
 * $arr = json_decode($json_raw,true);
 * print_r($arr);
 */
class RandomJson extends RandomToFile
{

    public $threshold_obj = 32768;

    public function __construct($file_name = null)
    {
        parent::__construct([$this, 'writeFileRandomJson']);
        $file_name = $this->setOutputFile($file_name);
    }

    public function genRandomJson(
        $min_elem_cnt = 3,
        $max_elem_cnt = 10,
        $threshold_nesting = 32768,
        $lim_depth = 3,
        $lim_elements = 100000,
        $threshold_obj = null
    ) {
        if (!\is_null($threshold_obj)) {
            $this->threshold_obj = $threshold_obj;
        }
        if (!$this->genRandomToFile(
                $min_elem_cnt,
                $max_elem_cnt,
                $threshold_nesting,
                $lim_depth,
                $lim_elements
            )
        ) {
            return false;
        }
        return $this->full_file_name;
    }

    public function writeFileRandomJson($parr)
    {
        static $keys = [];
        static $key_is_obj = [];

        static $need_div = 0;

        //extracting following work variables:
        \extract($parr); //$signal, $k, $v, $lim_depth, $root

        switch ($signal) {
            //siglan 'next' - output next scalar element of array [$k]=>$v
            case 'next':
                $is_obj = $key_is_obj[count($keys)];
                if ($is_obj) {
                    $out_str = json_encode([$k => $v]);
                    $out_str = substr($out_str, 1, -1);
                } else {
                    $out_str = json_encode($v);
                }
                if ($need_div) {
                    $out_str = ',' . $out_str;
                } else {
                    $need_div = true;
                }
                break;

            //signal 'open' - root or nested array beginning
            case 'open':
                //Generate [] array or {} ?
                $is_obj = (\mt_rand(0, 65535) >= $this->threshold_obj);
                $c = \count($keys);
                if ($c || !empty($root)) {
                    //nested array beginned
                    $prev_is_obj = isset($key_is_obj[$c]) ? $key_is_obj[$c] : 0;
                    $root = substr(json_encode([$root => '']), 1, -4);
                    array_push($keys, $root);
                }
                $key_is_obj[count($keys)] = $is_obj;
                $out_str = ($need_div) ? ',' : '';
                if (!empty($prev_is_obj)) {
                    $out_str .= $root . ":";
                }
                $out_str .= ($is_obj ? '{' : '[');
                $need_div = false;
                break;

            //signal 'close' - root or nested array ended
            case 'close':
                $is_obj = $key_is_obj[count($keys)];
                if (count($keys)) {
                    //nested array ended
                    \array_pop($keys);
                }
                $out_str = ($is_obj ? '}' : ']');
                break;

            //signal 'init' - when file open for write
            case 'init':
                $keys = [];
                $key_is_obj = [];
                $need_div = 0;
                $out_str = '';
        }
        //write formed string to output file
        \fwrite($fh, $out_str);
    }
}

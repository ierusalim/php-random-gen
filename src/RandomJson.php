<?php

namespace ierusalim\Random;

/**
 * This class coniains randomJson
 *
 * PHP Version 5.6
 * 
 * @package    ierusalim\randomJson
 * @author     Alexander Jer <alex@ierusalim.com>
 * @copyright  2017, Ierusalim
 * @license    https://opensource.org/licenses/Apache-2.0 Apache-2.0
 */
class RandomJson Extends RandomArray
{
    const J_LIT = 1;
    const J_NUM = 2;
    const J_STR = 4;
    const J_ARR = 8;
    const J_OBJ = 16;
    const J_BEG = 32;
    const J_END = 64;
    const J_KEY = 128;
    const J_DIV = 256;
    
    private $fileHandler;
    public $jsonFileName;
    
    public $out_for_wrMemoryFn = '';
    public $output_hook;
    protected $patt;
    protected $start_depth;
    protected $total_elements;
    /**
     * Set this limit to avoid generation unexpected too large arrays
     * 
     * @var integer
     */
    public $lim_elements = 100000;

    public function __construct( $init_patt = NULL, $output_fn = NULL) {
        if(empty($init_patt)) {
            $this->patt = [
                //literals
                J_LIT => ['true','false'], // 'null'
                //numbers
                J_NUM => [-32767,32767],
                //strings
                J_STR => [
                    'str_min_len'=>1,
                    'str_max_len'=>16,
                    'str_ava_chr'=>
                         'abcdefghijklmnopqrstuvwxyz'
                        .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                        .'0123456789-_'
                ],
                //arrays
                J_ARR => [
                    'min_elems'=>3,
                    'max_elems'=>10,
                    'en_types'=>[J_LIT, J_NUM, J_STR, J_ARR, J_OBJ],
                ],
                //named-key-arrays
                J_OBJ => [
                    'min_key_len'=>3,
                    'max_key_len'=>10,
                    'en_types'=>[J_LIT, J_NUM, J_STR, J_ARR, J_OBJ],
                    'key_ava_chr'=>'abcdefghijklmnopqrstuvwxyz'
                ]
            ];
        } else {
            $this->patt = $init_patt;
        }
        
        $init_charset = [
            1 => $this->patt[J_STR]['str_ava_chr'],
            2 => $this->patt[J_OBJ]['key_ava_chr'],
        ];
  
        parent::__construct($init_charset);
        if(is_callable($output_fn)) {
            $this->output_hook = $output_fn;
        } else {
            $this->setJsonMemory();
        }
    }
    public function setJsonMemory() {
        if(!empty($this->fileHandler)) {
            fclose($this->fileHandler);
            $this->fileHandler = NULL;
        }
        $this->output_hook = [$this,'wrMemoryFn'];
    }
    public function setJsonFile($fileName=NULL) {
        if(empty($fileName) || !is_string($fileName)) {
            $fileName = \sys_get_temp_dir() 
                . \DIRECTORY_SEPARATOR . md5(microtime()).'.json';
        }
        $this->jsonFileName = $fileName;
        $this->output_hook = [$this,'wrFileFn'];
    }
    public function wrFileFn($str,$j_type, $curr_depth) {
        switch($j_type) {
        case J_DIV:
            break;
        case J_LIT:
        case J_NUM:
            $this->total_elements++;
            break;
        case J_STR:
            $this->total_elements++;
            $str = '"'.$str.'"';
            break;
        case J_BEG:
            $str="\n".$ots.$str;
            break;
        case J_END:
            $str=$str."\n";
            break;
        case J_KEY:
            $str = '"'.$str.'":';
            break;
        }
        return fwrite($this->fileHandler, $str);
    }
    public function wrMemoryFn($str, $j_type, $curr_depth) {
        $ots = str_repeat(' ',$this->start_depth - $curr_depth);
        switch($j_type) {
        case J_DIV:
            $str.=" ";
            break;
        case J_LIT:
        case J_NUM:
            $this->total_elements++;
            break;
        case J_STR:
            $this->total_elements++;
            $str = '"'.$str.'"';
            break;
        case J_BEG:
        //    $this->total_elements++;
            $str="\n".$ots.$str;
            break;
        case J_END:
            $str=$str."\n";
            break;
        case J_KEY:
            $str = '"'.$str.'":';
            break;
        }
        $this->out_for_wrMemoryFn .= $str;
    }
    
    public function genRandomJson($max_depth=3, $root_obj=J_OBJ) {
        $this->start_depth = $max_depth;
        $this->total_elements = 0;
        if(empty($this->jsonFileName)) {
            $this->setJsonMemory();
        } else {
            $f = fopen($this->jsonFileName,'w');
            if(!$f) {
                throw new Exception("Error opening file ".$this->jsonFileName);
            }
            $this->fileHandler = $f;
        }
        $this->genRandomByType($root_obj, $max_depth);
        if(empty($this->fileHandler)) {
            return $this->out_for_wrMemoryFn;
        } else {
            fclose($this->fileHandler);
            $this->fileHandler = NULL;
            return file_get_contents($this->jsonFileName);
        }
    }
    public function genRandomByType(
        $j_type=J_OBJ,
        $left_depth=2
    ) {
        switch($j_type) {
        case J_LIT: //true, false, null
            $cnt = count($this->patt[J_LIT]);
            if(!$cnt) return false;
            $ret = rand(0,$cnt-1);
            $ret = $this->patt[J_LIT][$ret];
            call_user_func($this->output_hook, $ret, J_LIT, $left_depth);
            return true;
        case J_NUM:
            $ret = rand($this->patt[J_NUM][0], $this->patt[J_NUM][1]);
            call_user_func($this->output_hook, $ret, J_NUM, $left_depth);
            return true;
        case J_STR:
            $ret = $this->genRandomStr(rand(
                $this->patt[J_STR]['str_min_len'],
                $this->patt[J_STR]['str_max_len']
                ),1);
            call_user_func($this->output_hook, $ret, J_STR, $left_depth);
            return true;
        case J_ARR:
            if($left_depth<1) return false;
            $elements_total = rand(
                $this->patt[J_ARR]['min_elems'],
                $this->patt[J_ARR]['max_elems']
             );
            $en_types = $this->getPossibleTypes($j_type,$left_depth);
            $ent_cnt=count($en_types)-1;
            if($ent_cnt<0) return false;
            call_user_func($this->output_hook, '[', J_BEG, $left_depth);
            for($k=0; $k < $elements_total; $k++) {
               if($k) call_user_func($this->output_hook, ',', J_DIV, $left_depth);
                $j_type = $en_types[rand(0,$ent_cnt)];
                $this->genRandomByType($j_type,$left_depth-1);
                if($this->total_elements >= $this->lim_elements) break;
            }
            call_user_func($this->output_hook, ']', J_END, $left_depth);
            return true;
        case J_OBJ:
            if($left_depth<1) return false;
            $elements_total = rand(
                $this->patt[J_ARR]['min_elems'],
                $this->patt[J_ARR]['max_elems']
             );
            $en_types = $this->getPossibleTypes($j_type,$left_depth);
            $ent_cnt=count($en_types)-1;
            if($ent_cnt<0) return false;
            $keys_arr=[];
            call_user_func($this->output_hook, '{', J_BEG, $left_depth);
            for($k=0; $k < $elements_total; $k++) {
                if($k) call_user_func($this->output_hook, ',', J_DIV, $left_depth);
                for($t=0;$t<256;$t++) {
                    $key = $k.'-'.$this->genRandomStr(rand(
                                $this->patt[J_OBJ]['min_key_len'],
                                $this->patt[J_OBJ]['max_key_len']
                            ), 2);
                    //if(isset($keys_arr[$key]))continue;
                    //$keys_arr[$key]=1;
                    break;
                }
                $j_type = $en_types[rand(0,$ent_cnt)];
                call_user_func($this->output_hook, $key, J_KEY, $left_depth);
                if(!$this->genRandomByType($j_type,$left_depth-1)) {
                    call_user_func($this->output_hook, 'null', J_KEY, $left_depth);
                }
                if($this->total_elements >= $this->lim_elements) break;
            }
            call_user_func($this->output_hook, '}', J_END, $left_depth);
            return true;
        }
    }
    private function getPossibleTypes($j_type, $left_depth) {
        $en_types = $this->patt[$j_type]['en_types'];
        if($left_depth<2) {
            //REMOVE J_OBJ and J_ARR from enabled types
            $k=array_search(J_OBJ,$en_types);
            if($k !== false) unset($en_types[$k]);
            $k=array_search(J_ARR,$en_types);
            if($k !== false) unset($en_types[$k]);
        }
        return $en_types;
    }
}

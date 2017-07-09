<?php

namespace ierusalim\Random;

/**
 * This class contains RandomToFile
 *
 * PHP Version 5.6
 *
 * @package    ierusalim\RandomToFile
 * @author     Alexander Jer <alex@ierusalim.com>
 * @copyright  2017, Ierusalim
 * @license    https://opensource.org/licenses/Apache-2.0 Apache-2.0
 */
class RandomToFile extends RandomArray
{
    /**
     * Function for processing data and output to a file
     *
     * @var callable
     */
    public $fn_file_output;
    
    /**
     * File name for data output
     *
     * @var string
     */
    protected $full_file_name;
    
    /**
     * Handler of file opened for write
     *
     * @var resource|null
     */
    protected $file_handler;
    
    /**
     * Default extention for make file names
     * 
     * @var string
     */
    public $default_ext = '.json';

    /**
     * Main parameter of this class - function for processing and write data
     * By default, this hook is set as a function 'writeFileOutputExample',
     * which does the following function: output PHP code for assigning array.
     * By analogy, write the necessary output functions for the desired format
     *
     * @param callable|null $fn_write
     */
    public function __construct(callable $fn_write = null)
    {
        parent::__construct();
        if (is_null($fn_write)) {
            $this->fn_file_output = [$this, 'writeFileOutputExample'];
            $this->default_ext = '.php';
        } else {
            $this->fn_file_output = $fn_write;
        }
    }

    protected function writeFileOutputExample($parr)
    {
        static $keys=[];

        //extracting following work variables:
        \extract($parr); //$signal, $k, $v, $lim_depth, $root

        //begin formin output string
        $out_str = '$x';

        switch ($signal) {
            //siglan 'next' - output next scalar element of array [$k]=>$v
            case 'next':
                if (!\is_numeric($k)) {
                    $k = "'" . \addcslashes($k, "'\\") . "'";
                }
                if (!\is_numeric($v)) {
                    $v = "'" . \addcslashes($v, "'\\") . "'";
                }
                $out_str .= (count($keys) ?
                    '[' . implode('][', $keys) .']'
                    :
                    ''
                    ) . '['.$k.']=' . $v . ";\r\n";

                break;
            
            //signal 'begin' - root or nested array beginning
            case 'begin':
                if (count($keys) || !empty($root)) {
                    //nested array beginned
                    if (!is_numeric($root)) {
                        $root = "'" . \addcslashes($root, "'\\") . "'";
                    }
                    array_push($keys, $root);
                    $out_str .= '[' . implode('][', $keys) . ']';
                    $out_str .= "=[]; /* Create sub-array in key $out_str */\r\n";
                } else {
                    //root array beginned
                    $out_str .="=[]; /* CREATE ROOT OF ARRAY */\r\n";
                    $out_str = '<'."?php\n" . $out_str;
                }
                break;
                
            //signal 'end' - root or nested array ended
            case 'end':
                if (count($keys)) {
                    //nested array ended
                    $out_str = "/* end $out_str"
                              .'['. \implode('][', $keys) . ']'
                              . "*/\r\n";
                    \array_pop($keys);
                } else {
                    //root array ended
                    $out_str = " /*  END OF ARRAY */\r\n";
                }
                break;
            case 'init':
                $keys=[];
        }
        //write formed string to output file
        \fwrite($fh, $out_str);
    }
    
    public function genRandomToFile(
        $min_elem_cnt = 3,
        $max_elem_cnt = 10,
        $threshold = 32768,
        $lim_depth = 3,
        $lim_elements = 10000,
        $root = ''
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
        
        if (! $oh = $fh = $this->file_handler) {
            $this->openOutputFile();
            $fh = $this->file_handler;
            $signal = 'init';
            \call_user_func($this->fn_file_output,
                \compact('signal', 'fh', 'threshold', 'lim_depth', 'root')
            );
        }

        $elem_cnt = mt_rand($min_elem_cnt, $max_elem_cnt);
        if ($elem_cnt > $this->lim_elements) {
            $elem_cnt = $this->lim_elements;
        }

        $this->lim_elements-=$elem_cnt;
        
        $signal = 'begin';
        \call_user_func($this->fn_file_output,
            \compact('signal', 'fh', 'elem_cnt', 'lim_depth', 'root')
        );
         
        $signal = 'next';

        foreach ($this->genBigRange($elem_cnt) as $k => $v) {
            
            if ($this->keys_model) {
                if ($this->keys_model === 3) {
                    $k = \call_user_func($this->fn_gen_key,
                        \compact('k', 'v', 'threshold', 'lim_depth', 'root')
                        );
                } else {
                    $k = \mt_rand($this->min_arr_key, $this->max_arr_key);
                    if ($this->keys_model === 2) {
                        $k = $this->genRandomStr($k, 1);
                    }
                }
            }
            
            if ($v > $threshold || $lim_depth<2 || $this->lim_elements <2) {
                if ($this->values_model) {
                    if ($this->values_model == 3) {
                        $v = \call_user_func($this->fn_gen_value,
                            \compact('k', 'v', 'threshold', 'lim_depth', 'root')
                        );
                    } else {
                        $v = mt_rand($this->min_arr_val, $this->max_arr_val);
                        if ($this->values_model == 2) {
                            $v = $this->genRandomStr($v, 2);
                        }
                    }
                }
            } else {
                $this->genRandomToFile(
                    $min_elem_cnt,
                    $max_elem_cnt,
                    $threshold,
                    $lim_depth - 1,
                    0,
                    $k
                );
                continue;
            }

            \call_user_func($this->fn_file_output,
                \compact('signal', 'k', 'v', 'fh', 'lim_depth', 'root')
            );
        }

        $signal = 'end';
         \call_user_func($this->fn_file_output,
            \compact('signal', 'fh', 'elem_cnt', 'lim_depth', 'root')
         );

        if (! $oh) {
            $this->closeOutputFile();
        }
        return true;
    }

    protected function genBigRange($total_elements)
    {
        for ($k=1; $k<=$total_elements; $k++) {
            $v = \mt_rand(0, 65535);
            yield $k => $v;
        }
    }

    public function genTempFileName($ext = null)
    {
        $file_name = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'genRandom';
        if (!is_dir($file_name)) {
            if (!\mkdir($file_name)) {
                throw new \Exception("Can't mkdir $file_name for random file");
            }
        }
        if (is_null($ext)) {
            $ext = $this->default_ext;
        }
        $file_name.= DIRECTORY_SEPARATOR . md5(microtime()) . $ext;
        return $file_name;
    }

    public function setOutputFile($file_name = null, $ext = null)
    {
        if (empty($file_name) || !is_string($file_name)) {
            if(!is_null($ext)) $ext = $this->default_ext;
            $file_name = $this->genTempFileName($ext);
        }
        return $this->full_file_name = $file_name;
    }

    protected function openOutputFile()
    {
        if (! $this->file_handler = \fopen($this->full_file_name, 'w')) {
            throw new \Exception("Error write file ".$this->jsonFileName);
        }
        return $this->file_handler;
    }

    protected function closeOutputFile()
    {
        if ($this->file_handler) {
            \fclose($this->file_handler);
            $this->file_handler = false;
        }
    }
}

<?php

namespace ierusalim\Random;

/**
 * This class RandomStr is intended for generating random strings of any chars
 * Can generate following types:
 *  - random bytes  (as random_bytes PHP7-function)
 *  - ASCII chars from specified lists
 *  - any UTF-8 chars from specified lists (multibyte supported)
 *
 * PHP Version 5.6
 *
 *
 * @package   ierusalim\RandomGen
 * @author    Alexander Jer <alex@ierusalim.com>
 * @copyright 2017, Ierusalim
 * @license   https://opensource.org/licenses/Apache-2.0 Apache-2.0
 *
 *
 * Example of use:
 *
 *  $r = new RandomStr(); //initialized with default characters list (66 chars)
 *  echo $r->genRandomStr(99); //generate random 99 characters from default list
 *
 *  //It works fast. No problem for create random string of 1 million chars
 *  $str = $r->genRandomStr(1000000);
 *  echo "\nGenerated bytes: " . strlen($str);
 * 
 *  //Need multibyte characters? No problems, set flag $utf8mode = true
 *  $r->setChars("神會貓性少女 迪克和陰部", true);
 *  echo $r->genRandomStr(888);
 *
 */
class RandomStr
{
    /**
     * @var array
     */
    public $char_sets;
    /**
     * Function for generation random bytes
     *
     * @var callable
     */
    public $rnd_fn = __CLASS__.'::md5RandomBytes';
    
    /**
     * When creating an object, can specify a list of characters for generation.
     * Two formats are possible:
     *  - one string of chars;
     *  - strings array for many chars_set.
     *
     * When specified one string, it is means a list of character set nmb=0
     * When specified array, it is considered many character sets by array keys.
     * 
     * Its need for genRandomStr($len, $char_set_num=0) function.
     * 
     * For using multibyte UTF-8 characters set $utf8mode parameter to true 
     *
     * @param string|array $init_charset
     * @param boolean      $utf8
     */
    public function __construct($init_charset = null, $utf8mode = false)
    {
        //check available function for quick-generation random bytes
        if (function_exists('\random_bytes')) {
            //for PHP7
            $this->rnd_fn = '\random_bytes';
        } elseif (function_exists('\openssl_random_pseudo_bytes')) {
            //best for PHP5 variant, need OpenSSL ext.
            $this->rnd_fn = '\openssl_random_pseudo_bytes';
        } elseif (function_exists('\mcrypt_create_iv')) {
            //for PHP5, need MCrypt ext.
            $this->rnd_fn = '\mcrypt_create_iv';
        }
        $this->setChars($init_charset, $utf8mode);
    }

    /**
     * See description this parameters in description for __construct
     * 
     * @param string|array $init_charset
     * @param boolean      $utf8
     */
    public function setChars($init_charset = null, $utf8mode = false)
    {
        if (\is_array($init_charset)) {
            $this->char_sets = $init_charset;
        } elseif (is_string($init_charset)) {
            $this->char_sets = [$init_charset];
        } else {
            //by default used this characters
            $this->char_sets = [
                'abcdefghijklmnopqrstuvwxyz'.
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                '01234567890_-.'
                ];
        }

        if($utf8mode) {
            foreach($this->char_sets as $k=>$chars) {
                if(is_array($chars)) {
                    continue;
                }
                $len = \mb_strlen($chars, "UTF-8");
                if ($len == \strlen($chars)) {
                    continue;
                }
                $arr=[];
                for ($i = 0; $i < $len; $i++) {
                    $arr[] = \mb_substr($chars, $i, 1, "UTF-8");
                }
                $this->char_sets[$k]=$arr;
            }
        }
    }

    /**
     * Random string generate (specified length and optional characters-set nmb)
     *
     * Available characters placed in the strings array $this->char_sets
     * This array initializing when creating new RandomStr object.
     * This array is available as a public parameter and can modified like
     *  $r->char_sets[nmb] = 'string of avaliable characters for nmb'
     *
     * @param integer $len Length of generate string
     * @param integer $char_set_num Number of char_set selected for use
     *
     * @api
     * @staticvar string $chars
     *
     * @return string
     */
    public function genRandomStr($len = 10, $char_set_num = 0)
    {
        if(!isset($this->char_sets[$char_set_num]) || $len<1) {
            return false;
        } elseif(is_array($this->char_sets[$char_set_num])) {
            $l = count($this->char_sets[$char_set_num]);
        } else {
            $l = \strlen($this->char_sets[$char_set_num]);
        }
        if (! $l) {
            return false;
        }
        $outstr = '';
        foreach (\unpack('v*', call_user_func($this->rnd_fn, $len * 2)) as $n) {
            $outstr .= $this->char_sets[$char_set_num][$n % $l];
        }
        return $outstr;
    }
    
    /**
     * Analog of PHP7-function random_bytes($length) for using under PHP5
     *
     * @param integer $length
     * @return string
     */
    public function genRandomBytes($length)
    {
        return call_user_func($this->rnd_fn, $length);
    }

    /**
     * This function is used to generate bytes if the best functions not found
     *
     * @param integer $len
     * @return string
     */
    public function md5RandomBytes($len)
    {
        $start = mt_rand(9, PHP_INT_MAX);
        $output = '';
        for ($i=$start; strlen($output) < $len; $i++) {
            $output .= md5($i, true);
        }
        return substr($output, 0, $len);
    }
}

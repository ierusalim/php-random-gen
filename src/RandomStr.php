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
class RandomStr
{
    /**
     * @var array
     */
    protected $char_sets;
    /**
     * Function for generation random bytes
     * 
     * @var callable
     */
    public $rnd_fn = __CLASS__.'::hmac_random_pseudo_bytes';
    
    /**
     * When creating an object, can specify a list of characters for generation.
     * Two formats are possible:
     *  - one string of chars;
     *  - strings array with numeric keys.
     * When specified one string, it is considered a list of characters number 0
     * When specified array, can set characters for many character lists by nmb.
     * Its need for main generation function genRandomStr($len, $char_set_num=0)
     * instead a list of characters must specify its number (0 by default).
     * 
     * @param string|array $init_charset
     */
    public function __construct($init_charset = null)
    {
        if(is_array($init_charset)) {
            $this->char_sets = $init_charset;
        } elseif(is_string($init_charset)) {
            $this->char_sets = [$init_charset];
        } else {
            //by default used this characters
            $this->char_sets = [
                'abcdefghijklmnopqrstuvwxyz'.
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                '01234567890_-.'
                ];
        }
        
        //check available function for quick-generation random bytes
        if(function_exists('\random_bytes')) {
            //for PHP7
            $this->rnd_fn = '\random_bytes';
        } elseif(function_exists('\openssl_random_pseudo_bytes')) {
            //for PHP5 (best variant, need OpenSSL)
            $this->rnd_fn = '\openssl_random_pseudo_bytes';
        } elseif(function_exists('\mcrypt_create_iv')) {
            //for PHP5 (need MCrypt)
            $this->rnd_fn = '\mcrypt_create_iv';
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
        if(! $l = \strlen($this->char_sets[$char_set_num])) return false;
        $outstr = '';
        foreach (\unpack('v*', call_user_func($this->rnd_fn, $len * 2)) as $n) {
            $outstr .= $this->char_sets[$char_set_num][$n % $l];
        }
        return $outstr;
    }

    /**
     * This function is used to generate bytes if the best functions not found
     * 
     * @param integer $len
     * @return string
     */
    function hmac_random_pseudo_bytes($len) {
        $start = mt_rand(9, PHP_INT_MAX);
        $output = '';
        for ($i=$start; strlen($output) < $len; $i++)
            $output .= hash_hmac('sha1', $i, $seed, true);
        return substr($output, 0, $len);
    }
}

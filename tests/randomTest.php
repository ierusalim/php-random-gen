<?php

namespace ierusalim\Random;

/**
 * This class coniains randomTest
 *
 * PHP Version 5.6
 * 
 * @package    ierusalim\randomTest
 * @author     Alexander Jer <alex@ierusalim.com>
 * @copyright  2017, Ierusalim
 * @license    https://opensource.org/licenses/Apache-2.0 Apache-2.0
 */
class randomTest
{
    //code here
}
require_once '../vendor/autoload.php';

//$g = (new Generator())->genRandomStr(10);
$g = randomGen::genRandomStrArrStrKeys(5,5,1);

print_r($g);


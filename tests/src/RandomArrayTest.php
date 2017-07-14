<?php

namespace ierusalim\Random;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-07-08 at 10:22:28.
 */
class RandomArrayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RandomArray
     */
    protected $object;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new RandomArray;
    }

    /**
     * @covers ierusalim\Random\RandomArray::countArrayValuesRecursive
     * @todo   Implement testCountArrayValuesRecursive().
     */
    public function testCountArrayValuesRecursive()
    {
        $r = $cnt = $this->object;
        $arr = range(0, 99);
        $cnt = $r->countArrayValuesRecursive($arr);
        $this->assertTrue($cnt == 100);

        $r->setKeysModel(1, null);
        $r->setValuesModel();

        $arr = $r->genRandomArray(2, 2, 65535, 6);
        $cnt = $r->countArrayValuesRecursive($arr);
        $this->assertTrue($cnt == 64);

        $arr = $r->genRandomArray(2, 2, 65535, 4);
        $cnt = $r->countArrayValuesRecursive($arr);
        $this->assertTrue($cnt == 16);

        $arr = $r->genRandomArray(2, 2, 65535, 1);
        $cnt = $r->countArrayValuesRecursive($arr);
        $this->assertTrue($cnt == 2);
    }

    public function testcountArrayMaxDepth()
    {
        $arr = range(0, 10);
        $d = $this->object->countArrayMaxDepth($arr);
        $this->assertTrue($d === 0);

        $d = $this->object->countArrayMaxDepth('abcd');
        $this->assertFalse($d);

        $arr = [[1]];
        $d = $this->object->countArrayMaxDepth($arr);
        $this->assertTrue($d === 1);

        $arr = [[[]], [[]], []];
        $d = $this->object->countArrayMaxDepth($arr);
        $this->assertTrue($d === 2);

        $arr = [[[]], [[]], [], [1, [1, [1, [1, 2], 2], 2], 2], 2];
        $d = $this->object->countArrayMaxDepth($arr);
        $this->assertTrue($d === 4);

        $this->object->setKeysModel(1, null);
        $this->object->setValuesModel();

        $depth_limit = 21;
        $arr = $this->object->genRandomArray(2, 2, 65535, $depth_limit, 100);
        $d = $this->object->countArrayMaxDepth($arr);
        $this->assertTrue($d === $depth_limit - 1);
    }

    public function testConstructUrf8()
    {
        // init utf-8 charset for array values, not for keys
        $r = new RandomArray('一二三', true);
        $arr = $r->genRandomArray(1, 1);
        $this->assertEquals(1, count($arr));
    }
    /**
     * @covers ierusalim\Random\RandomArray::setKeysModel
     * @todo   Implement testSetKeysModel().
     */
    public function testSetKeysModel()
    {
        $r = $this->object;
        //testing numeric keys range
        $min_key = 11;
        $max_key = 19;
        $r->setKeysModel($min_key, $max_key);
        $arr = $r->genRandomArray(222, 222, 0);
        $this->assertTrue(count($arr) == 9);

        //checking minimal and maximal key in generated array
        $min = 7777777;
        $max = 0;
        array_walk_recursive ($arr, function ($v, $k) use (&$min, &$max) {
            $t = $k;
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min === $min_key);
        $this->assertTrue($max === $max_key);

        //testing random-string keys
        $min_key = 11;
        $max_key = 12;
        $el_cnt = 100;
        $r->setKeysModel($min_key, $max_key, implode(range('a', 'z')));
        $arr = $r->genRandomArray($el_cnt, $el_cnt, 0);
        $this->assertTrue(count($arr) === $el_cnt);

        //checking min and max key length
        $min = 7777777;
        $max = 0;
        array_walk_recursive($arr, function ($v, $k) use (&$min, &$max) {
            $t = strlen($k);
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min === $min_key);
        $this->assertTrue($max === $max_key);

        //testing numeric keys plain range(1, el_cnt)
        $min_key = 1;
        $max_key = null;
        $r->setKeysModel($min_key, $max_key);
        $el_cnt = 100;
        $arr = $r->genRandomArray($el_cnt, $el_cnt, 0);
        $this->assertTrue(count($arr) == $el_cnt);

        //counting minimal and maximal key
        $min = 7777777;
        $max = 0;
        $err = false;
        array_walk_recursive($arr, function ($v, $k) use (&$min, &$max, &$err) {
            static $prev = false;
            $t = $k;
            if ($prev === false) {
                $prev = $t;
            } else {
                if (($prev + 1) != $t) {
                    $err = true;
                }
            }
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min === $min_key);
        $this->assertTrue($max === $el_cnt);

        // utf-8 keys generate with numeric values
        $r->setKeysModel(2, 5, '하나 둘 셋', true);
        $r->setValuesModel();
        $arr = $r->genRandomArray(3, 3, 0);
        $this->assertEquals(3, count($arr));
    }

    /**
     * @covers ierusalim\Random\RandomArray::setValuesModel
     * @todo   Implement testSetValuesModel().
     */
    public function testSetValuesModel()
    {
        $r = $this->object;
        
        //set plain keys model
        $r->setKeysModel(1, null);

        //test numeric values range
        $min_val = 11;
        $max_val = 19;
        $el_cnt = 222;
        $r->setValuesModel($min_val, $max_val);
        $arr = $r->genRandomArray($el_cnt, $el_cnt, 0);
        $this->assertTrue(count($arr) == $el_cnt);

        //Check min and max values in generated array
        $min = 7777777;
        $max = 0;
        array_walk_recursive($arr, function ($v, $k) use (&$min, &$max) {
            $t = $v;
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min === $min_val);
        $this->assertTrue($max === $max_val);

        //test default numeric values base range 0..65535
        $el_cnt = 222;
        $on_cnt = (int) ($el_cnt / 4);
        $r->setValuesModel();
        $arr = $r->genRandomArray($on_cnt, $on_cnt, 32768, 4, $el_cnt);
        //print_r($arr);
        $d = $r->countArrayMaxDepth($arr);
        //echo "Max depth=$d \n";
        $this->assertTrue(count($arr) < $el_cnt);
        $real_el_cnt = $r->countArrayValuesRecursive($arr);
        $this->assertTrue($real_el_cnt <= $el_cnt);
        $this->assertTrue($real_el_cnt >= $on_cnt);
        $min = 7777777;
        $max = 0;
        array_walk_recursive($arr, function ($v, $k) use (&$min, &$max) {
            $t = $v;
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min >= 0);
        $this->assertTrue($max < 65536);

        //test random-string values
        $min_val = 11;
        $max_val = 12;
        $el_cnt = 100;
        $r->setValuesModel($min_val, $max_val, implode(range('a', 'z')));
        $arr = $r->genRandomArray($el_cnt, $el_cnt, 0);
        $this->assertTrue(count($arr) === $el_cnt);
        $min = 7777777;
        $max = 0;
        array_walk_recursive($arr, function ($v, $k) use (&$min, &$max) {
            $t = strlen($v);
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min === $min_val);
        $this->assertTrue($max === $max_val);

        // utf-8 values generate with numeric keys
        $r->setKeysModel();
        $r->setValuesModel(2, 5, '하나 둘 셋', true);
        $arr = $r->genRandomArray(3, 3, 0);
        $this->assertEquals(3, count($arr));
    }

    /**
     * @covers ierusalim\Random\RandomArray::genRandomArray
     * @todo   Implement testGenRandomArray().
     */
    public function testGenRandomArray()
    {
        $r = $this->object;

        //error: depth-limit are 0
        $this->assertFalse($r->genRandomArray(0, 1, 0, 0));
        //request 2 elements, but lim-elements = 1, must return 1 element
        $this->assertEquals(1, count($r->genRandomArray(2, 2, 0, 1, 1)));
        
        $r->setKeysModel(1, null);
        $r->setValuesModel();

        $on_cnt = 10;
        $arr = $r->genRandomArray($on_cnt, $on_cnt, 32768, 9, 100);
        $d = $r->countArrayMaxDepth($arr);
        $this->assertEquals($d, 8);

        $on_cnt = 1000;
        $not_cnt = 0;
        for ($t=0; $t<10; $t++) {
            $arr = $r->genRandomArray($on_cnt, $on_cnt, 0);
            $c = $r->countArrayValuesRecursive($arr);
            if ($c != $on_cnt) {
                $not_cnt++;
            } else {
                break;
            }
        }
        $this->assertEquals($c, $on_cnt);
        $this->assertGreaterThan($not_cnt, 5);
        $d = $r->countArrayMaxDepth($arr);
        $this->assertEquals($d, 0);

        $min = 7777777;
        $max = 0;
        array_walk_recursive($arr, function ($v, $k) use (&$min, &$max) {
            $t = $v;
            if ($t > $max) {
                $max = $t;
            }
            if ($t < $min) {
                $min = $t;
            }
        });
        $this->assertTrue($min >= 0);
        $this->assertTrue($max <= 65535);

        $on_cnt = 10;
        $elim = 1000;
        $arr = $r->genRandomArray($on_cnt, $on_cnt, 32768, 3, $elim);
        $c = $r->countArrayValuesRecursive($arr);
        $this->assertTrue($c > $on_cnt);
        $this->assertTrue($c <= $elim);
        $d = $r->countArrayMaxDepth($arr);
        $this->assertEquals($d, 2);
        
        //test generate fn-key fn-value array
        $r->setKeysModelFn(function ($parr) {
            \extract($parr); //$k, $v, $lim_depth
            return \md5($k);
        });
        $r->setValuesModelFn(function ($parr) {
            return $parr;
        });
        $arr = $r->genRandomArray();
        $n = 1;
        $err_cnt = 0;
        foreach ($arr as $k => $v) {
            if ($k != \md5($n)) {
                $err_cnt++;
            }
            $n++;
        }
        $this->assertEquals($err_cnt, 0);

        // utf-8 keys and utf-8 values
        $r->setKeysModel(8, 8, '一二三', true);
        $r->setValuesModel(1, 5, '一二三', true);
        $arr = $r->genRandomArray(3, 3, 0, 1);
        $this->assertEquals(3, count($arr));
    }

    /**
     * @covers ierusalim\Random\RandomArray::setKeysModelFn
     * @todo   Implement testSetKeysModelFn().
     */
    public function testSetKeysModelFn()
    {
        $this->object->setKeysModelFn(function ($parr) {
            \extract($parr); //$k, $v, $lim_depth
            return \md5($k);
        });
        $this->object->setValuesModel();
        $arr = $this->object->genRandomArray(20, 20, 32768, 2);
        $n = 1;
        $err_cnt = 0;
        foreach ($arr as $k => $v) {
            if ($k != \md5($n)) {
                $err_cnt++;
            }
            $n++;
        }
        $this->assertEquals($err_cnt, 0);
    }

    /**
     * @covers ierusalim\Random\RandomArray::setValuesModelFn
     * @todo   Implement testSetValuesModelFn().
     */
    public function testSetValuesModelFn()
    {
        $this->object->setKeysModel();
        $this->object->setValuesModelFn(function ($parr) {
            \extract($parr); //$k, $v, $lim_depth
            return $lim_depth;
        });
        $arr = $this->object->genRandomArray(2, 4, 32768, 5);
        $err_cnt = 0;
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $l) {
                    if (!is_array($l) && ($l !== 4)) {
                        $err_cnt++;
                    }
                }
            } else {
                if ($v !== 5) {
                    $err_cnt++;
                }
            }
        }
        $this->assertEquals($err_cnt, 0);
    }
}

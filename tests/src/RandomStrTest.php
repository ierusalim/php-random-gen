<?php

namespace ierusalim\Random;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-07-07 at 21:57:01.
 */
class RandomStrTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RandomStr
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new RandomStr();
    }

    /**
     * @covers ierusalim\Random\RandomStr::genRandomStr
     * @todo   Implement testGenRandomStr().
     */
    public function testGenRandomStr()
    {
        for ($len = 0; $len < 10; $len++) {
            $bytes = $this->object->genRandomStr($len);
            if ($len) {
                $this->assertTrue(is_string($bytes));
                $this->assertTrue(strlen($bytes) == $len);
            } else {
                $this->assertFalse($bytes);
            }
        }
        for ($len = 100; $len < 100000; $len += (int) ($len / 7)) {
            $bytes = $this->object->genRandomStr($len);
            $this->assertTrue(strlen($bytes) == $len);
        }
        $prev = '';
        $len = 7;
        for ($i = 0; $i < 100; $i++) {
            $bytes = $this->object->genRandomStr($len);
            $this->assertFalse($bytes === $prev);
            $prev = $bytes;
        }
    }

    /**
     * @covers ierusalim\Random\RandomStr::genRandomBytes
     * @todo   Implement testGenRandomBytes().
     */
    public function testGenRandomBytes()
    {
        for ($len = 0; $len < 100000; $len += (int) ($len / 10) + 1) {
            $bytes = $this->object->genRandomBytes($len);
            if ($len) {
                $this->assertTrue(is_string($bytes));
                $this->assertTrue(strlen($bytes) == $len);
            } else {
                $this->assertFalse($bytes);
            }
        }
    }

    /**
     * @covers ierusalim\Random\RandomStr::md5RandomBytes
     * @todo   Implement testMd5RandomBytes().
     */
    public function testMd5RandomBytes()
    {
        for ($len = 0; $len < 100000; $len += $len + 1) {
            $bytes = $this->object->md5RandomBytes($len);
            if ($len) {
                $this->assertTrue(is_string($bytes));
                $this->assertTrue(strlen($bytes) == $len);
            } else {
                $this->assertTrue(!is_string($bytes));
                $this->assertFalse($bytes);
            }
        }
    }
}
<?php

namespace ierusalim\Random;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-07-10 at 09:06:50.
 */
class RandomJsonTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RandomJson
     */
    protected $object;

    /**
      * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new RandomJson();
    }

    public function testConstructWithFileName()
    {
        $file_name = $this->object->genTempFileName('.json');
        $r = new RandomJson($file_name);
        $this->assertEquals($r->full_file_name, $file_name);
    }
    /**
     * @covers ierusalim\Random\RandomJson::writeFileRandomJson
     */
    public function testWriteFileRandomJson()
    {
        $r = $this->object;
        $r->threshold_obj = 0;
        $r->openOutputFile();
        $fh = $r->file_handler;
        $v = $k = $lim_depth = $root = '1';
        foreach ([
            'init',
            'open',
            'open',
            'next',
            'next',
            'close',
            'next',
            'close'
            ] as $signal) {
            $r->writeFileRandomJson(
               \compact('signal', 'fh', 'v', 'k', 'lim_depth', 'root')
            );
            $k++;
            if ($k>2) {
                 $r->threshold_obj = 65535;
            }
        }
        $r->closeOutputFile();
        $file_name = $r->full_file_name;
        \unlink($file_name);
    }

    public function testMakeNextValueStr()
    {
        $r = $this->object;
        $need_div = 0;
        $is_obj = 1;
        $out_str = $r->makeNextValueStr('a', 'b', $is_obj, $need_div);
        $this->assertEquals('"a":"b"', $out_str);
        $is_obj = 0;
        $out_str = $r->makeNextValueStr(1, 2, $is_obj, $need_div);
        $this->assertEquals(',2', $out_str);
    }
    /**
     * @covers ierusalim\Random\RandomJson::genRandomJson
     * @todo   Implement testGenRandomJson().
     */
    public function testGenRandomJson()
    {
        $g = $this->object;
        $g->setKeysModel();
        $g->setValuesModel();
        
        // test failure
        $this->assertFalse($g->genRandomJson(0, 0, 0, 0, 0, 1));
        
        // test normal generation
        $lim_elem = 1000;
        for ($t=0; $t<10; $t++) {
            $file_name = $g->genRandomJson(10, 10, 32768, 3, $lim_elem);
            $this->assertNotEmpty($file_name);
            $this->assertFileExists($file_name);
            $file_size = filesize($file_name);
            $this->assertGreaterThan(100, $file_size);
            $json_raw = file_get_contents($file_name);
            $this->assertEquals(strlen($json_raw), $file_size);
            $this->assertJson($json_raw);
            $arr = json_decode($json_raw, true);
            $this->assertTrue(is_array($arr));
            $this->assertEquals(count($arr), 10);
            $d = $g->countArrayMaxDepth($arr);
            unlink($file_name);
            if ($d == 2) {
                break;
            }
        }
        $this->assertTrue($t<3);
    }
}

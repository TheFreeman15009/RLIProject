<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Controller;

class ControllerTest extends TestCase
{
    public function testMillisToStandard()
    {
        $k = new Controller();
        $this->assertEquals($k->convertMillisToStandard(1), "0.001");
        $this->assertEquals($k->convertMillisToStandard(-1), "-0.001");
        $this->assertEquals($k->convertMillisToStandard(10), "0.010");
        $this->assertEquals($k->convertMillisToStandard(100), "0.100");
        $this->assertEquals($k->convertMillisToStandard(999), "0.999");
        $this->assertEquals($k->convertMillisToStandard(1000), "1.000");
        $this->assertEquals($k->convertMillisToStandard(59999), "59.999");
        $this->assertEquals($k->convertMillisToStandard(60001), "1:00.001");
        $this->assertEquals($k->convertMillisToStandard(600001), "10:00.001");
        $this->assertEquals($k->convertMillisToStandard(-600001), "-10:00.001");
    }

    public function testSgnp()
    {
        $k = new Controller();
        $this->assertEquals($k->sgnp(0.1), 1);
        $this->assertEquals($k->sgnp(0), 1);
        $this->assertEquals($k->sgnp(-0.1), -1);
    }

    public function testStandardToMillis()
    {
        $k = new Controller();
        $this->assertEquals($k->convertStandardtoMillis("0.001"), 1);
        $this->assertEquals($k->convertStandardtoMillis("-0.001"), -1);
        $this->assertEquals($k->convertStandardtoMillis("0.010"), 10);
        $this->assertEquals($k->convertStandardtoMillis("0.100"), 100);
        $this->assertEquals($k->convertStandardtoMillis("0.999"), 999);
        $this->assertEquals($k->convertStandardtoMillis("1.000"), 1000);
        $this->assertEquals($k->convertStandardtoMillis("59.999"), 59999);
        $this->assertEquals($k->convertStandardtoMillis("1:00.001"), 60001);
        $this->assertEquals($k->convertStandardtoMillis("10:00.001"), 600001);
        $this->assertEquals($k->convertStandardtoMillis("-10:00.001"), -600001);
    }

    public function testSortByKey()
    {
        $k = new Controller();

        $arrWithNoKey = [['b' => 3], ['b' => 2]];
        $k->sortByKey($arrWithNoKey, 'a');
        $this->assertEquals($arrWithNoKey, [['b' => 3], ['b' => 2]]);

        $arrToBeSortedInAscendingOrder = [['a' => 3, 'b' => 1], ['a' => 2]];
        $k->sortByKey($arrToBeSortedInAscendingOrder, 'a');
        $this->assertEquals($arrToBeSortedInAscendingOrder, [['a' => 2], ['a' => 3, 'b' => 1]]);

        $arrToBeSortedInDescendingOrder = [['a' => 2], ['a' => 3, 'c' => 1]];
        $k->sortByKey($arrToBeSortedInDescendingOrder, 'a', -1);
        $this->assertEquals($arrToBeSortedInDescendingOrder, [['a' => 3, 'c' => 1], ['a' => 2]]);
    }
}

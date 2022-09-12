<?php
namespace OCA\AppDirect\Tests;

use OCA\AppDirect\Database\GroupMapper;

/**
 *  Start test with: phpunit tests/datetest.php
 */
class DateTest extends \Test\TestCase {
    protected $groupMapper;

    protected function setUp() {
        parent::setUp();
        $this->groupMapper = new GroupMapper();
    }

    public function testBilling(){
        $this->assertEquals(1241421, $this->groupMapper->calcBilling(1656506095,1656506095));
        $this->assertEquals(1241421, $this->groupMapper->calcBilling(1656506095,1656506095));
        $this->assertEquals(1241421, $this->groupMapper->calcBilling(1656506095,1656506095));
        $this->assertEquals(1241421, $this->groupMapper->calcBilling(1656506095,1656506095));
        $this->assertEquals(1241421, $this->groupMapper->calcBilling(1656506095,1656506095));
    }
}
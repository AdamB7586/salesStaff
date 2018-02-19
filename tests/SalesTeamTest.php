<?php
namespace Staff\Tests;

use Staff\SalesTeam;
use DBAL\Database;
use PHPUnit\Framework\TestCase;

class SalesTeamTest extends TestCase{
    
    protected $db;
    protected $salesTeam;
    
    /**
     * @covers \Staff\SalesTeam::__construct
     */
    public function setUp() {
        $this->db = new Database($GLOBALS['HOSTNAME'], $GLOBALS['USERNAME'], $GLOBALS['PASSWORD'], $GLOBALS['DATABASE']);
        if(!$this->db->isConnected()){
             $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        $this->db->query(file_get_contents(dirname(dirname(__FILE__)).'/database/sales_staff.sql'));
        $this->db->query(file_get_contents(dirname(__FILE__).'/sample_data/staff.sql'));
        $this->salesTeam = new SalesTeam($this->db);
    }
    
    protected function tearDown() {
        $this->db = null;
        $this->salesTeam = null;
    }
    
    /**
     * @covers \Staff\SalesTeam::__construct
     * @covers \Staff\SalesTeam::numStaff
     */
    public function testCountNumberOfSalesStaff(){
        $this->assertGreaterThanOrEqual(0, $this->salesTeam->numStaff());
    }
    
    /**
     * @covers \Staff\SalesTeam::__construct
     * @covers \Staff\SalesTeam::getStaffName
     */
    public function testGetStaffName(){
        $this->assertEquals('George Michael', $this->salesTeam->getStaffName(2));
        $this->assertEquals('Amy Hope', $this->salesTeam->getStaffName(4));
        $this->assertFalse($this->salesTeam->getStaffName(1526));
    }
    
    /**
     * @covers \Staff\SalesTeam::__construct
     * @covers \Staff\SalesTeam::listStaff
     */
    public function testListStaff(){
        $staff = $this->salesTeam->listStaff();
        $this->assertArrayHasKey('staffid', $staff[1]);
        $this->assertArrayHasKey('fullname', $staff[0]);
        $this->assertEquals(3, $staff[2]['staffid']);
    }
    
    /**
     * @covers \Staff\SalesTeam::__construct
     * @covers \Staff\SalesTeam::getStaffHours
     */
    public function testGetStaffHours(){
        $this->assertFalse($this->salesTeam->getStaffHours(52));
        $staff_hours = $this->salesTeam->getStaffHours(3);
        $this->assertArrayHasKey('monday', $staff_hours);
        $this->assertEquals('19:00:00', $staff_hours['friday']);
        
        $second_staff_hours = $this->salesTeam->getStaffHours(2);
        $this->assertArrayHasKey('wednesday', $second_staff_hours);
        $this->assertEquals('17:00:00', $second_staff_hours['friday']);
    }
    
    /**
     * @covers \Staff\SalesTeam::__construct
     * @covers \Staff\SalesTeam::getActiveStaff
     * @covers \Staff\SalesTeam::numStaff
     * @covers \Staff\SalesTeam::numActiveStaffToday
     * @covers \Staff\SalesTeam::getStaffInfo
     * @covers \Staff\SalesTeam::dayAndTime
     * @covers \Staff\SalesTeam::dayNo
     * @covers \Staff\SalesTeam::__call
     */
    public function testGetActiveStaff(){
        $activeStaff = $this->salesTeam->getActiveStaff();
        $this->assertArrayHasKey('staffid', $activeStaff);
        $this->assertEquals(1, $activeStaff['staffid']);
    }
}

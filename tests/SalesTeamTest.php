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
     * @covers \Staff\SalesTeam::numStaff
     */
    public function testCountNumberOfSalesStaff(){
        $this->assertGreaterThanOrEqual(0, $this->salesTeam->numStaff());
    }
    
    /**
     * @covers \Staff\SalesTeam::getStaffName
     */
    public function testGetStaffName(){
        $this->assertEquals('George Michael', $this->salesTeam->getStaffName(2));
        $this->assertEquals('Amy Hope', $this->salesTeam->getStaffName(4));
    }
    
    /**
     * @covers \Staff\SalesTeam::listStaff
     */
    public function testListStaff(){
        $staff = $this->salesTeam->listStaff();
        $this->assertArrayHasKey('staffid', $staff[1]);
        $this->assertArrayHasKey('fullname', $staff[0]);
        $this->assertEquals(3, $staff[2]['staffid']);
    }
}

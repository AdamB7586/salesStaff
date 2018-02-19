<?php
namespace Staff\Tests;

use Staff\SalesTeam;
use DBAL\Database;
use PHPUnit\Framework\TestCase;

class SalesTeamTest extends TestCase{
    
    protected $db;
    protected $salesTeam;
    
    public function setUp() {
        $this->db = new Database($GLOBALS['HOSTNAME'], $GLOBALS['USERNAME'], $GLOBALS['PASSWORD'], $GLOBALS['DATABASE']);
        if(!$this->db->isConnected()){
             $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        $this->db->query(file_get_contents('./database/sales_staff.sql'));
        $this->db->query(file_get_contents('sample_data/staff.sql'));
        $this->salesTeam = new SalesTeam($this->db);
    }
    
    protected function tearDown() {
        $this->db = null;
        $this->salesTeam = null;
    }
    
    public function testCountNumberOfSalesStaff(){
        $this->assertGreaterThanOrEqual(0, $this->salesTeam->numStaff());
    }
    
    public function testGetStaffName(){
        $this->assertEquals('George Michael', $this->salesTeam->getStaffName(2));
        $this->assertEquals('Amy Hope', $this->salesTeam->getStaffName(4));
    }
}

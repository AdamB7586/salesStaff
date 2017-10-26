<?php
namespace Staff\Tests;

use Staff\SalesTeam;
use DBAL\Database;
use PHPUnit\Framework\TestCase;

class SalesTeamTest extends TestCase{
    
    protected $db;
    protected $salesTeam;
    
    public function setUp() {
        $this->db = new Database('', '', '', '', false, false, true, 'sqlite');
        if(!self::$db->isConnected()){
             $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        $this->salesTeam = new SalesTeam($this->db);
    }
    
    public function test_count_number_of_sales_satff(){
        $this->assertGreaterThanOrEqual(0, $this->salesTeam->numStaff());
    }
}

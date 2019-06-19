<?php
namespace Staff;

use DBAL\Database;

class SalesTeam{
    protected $db;
    protected $staffinfo;
    
    const STAFFTABLE = 'sales_staff';
    const STAFFHOURSTABLE = 'sales_staff_hours';
    
    public $staffDefault = ['id' => 99, 'fullname' => 'Default Name', 'firstname' => 'Default', 'email' => 'staff.email@example.com'];
    
    /**
     * This class is used for use with the sales team members
     * @param Database $db Add an instance of the database connection class
     */
    public function __construct(Database $db){
        $this->db = $db;
    }
    
    /**
     * Returns the number of sales staff in the database
     * @return int Returns the number of sales staff 
     */
    public function numStaff(){
        return $this->db->count(self::STAFFTABLE);
    }
    
    /**
     * Gets the next active staff member 
     * @param string $type Should be set to either 'enquiry' or 'sale' the default is 'sale'
     * @return array Returns the Staff members information as an array includes 'fullname', 'firstname', 'email' and 'staffid'
     */
    public function getActiveStaff($type = 'sale'){       
        if($this->numStaff() >= 1){
            $activestaff = $this->numActiveStaffToday($type);
            $updateName = 'updateLast'.$type;
            if($activestaff['next'] >= 1){
                $staff = $this->getStaffInfo(['staffid' => $activestaff['next']]);
                $this->$updateName($staff['id']);
                return $staff;
            }
             // Nobody active do them all
            $staff = $this->getStaffInfo(['staffid' => ['>', $this->{'last'.$type.'ID'}]], ['staffid' => 'ASC']);
            if($staff['id'] != $this->staffDefault['id']){ // Not the last one so do the next
                $this->$updateName($staff['id']);
                return $staff;
            }
            $newStaff = $this->getStaffInfo([], ['staffid' => 'ASC']); // Last one so start from beginning
            $this->$updateName($newStaff['id']);
            return $newStaff;
        }
        return $this->getStaffInfo(); // No current users in the database use default
    }
    
    /**
     * Returns the staff information for a given staff members id
     * @param int $id This should be the unique staff id
     * @return array|boolean If the staff ID exists and is valid will return the array of information else returns false
     */
    public function getStaffInfoByID($id) {
        if(is_numeric($id)){
            return $this->getStaffInfo(['staffid' => $id]);
        }
        return false;
    }
    
    /**
     * Returns the staff information for the given variables
     * @param array $where Should be in the form of a where query e.g. array('active' => '1', etc)
     * @param array $order Should be in the form of a order query e.g. array('staffid' => 'ASC')
     * @return array Returns the Staff members information as an array includes 'fullname', 'firstname', 'email' and 'staffid'
     */
    protected function getStaffInfo($where = [], $order = []){
        $staff = $this->db->select(self::STAFFTABLE, $where, '*', $order);
        if($staff['staffid']){
            $this->staffinfo = $staff;
            $this->staffinfo['id'] = $staff['staffid'];
            return $this->staffinfo;
        }
        return $this->staffDefault;
    }
    
    /**
     * Returns the number of Staff who are currently active today
     * @param string $type Should be set as either enquiry or sale depending on what was kind of transaction the user is being search for
     * @return int Returns the sales staff ID of the person who should receive this transaction/enquiry 
     */
    protected function numActiveStaffToday($type){
        $data = array();
        $dateInfo = $this->dayAndTime();        
        $activestaff = $this->db->selectAll(self::STAFFHOURSTABLE, [$dateInfo['day'] => ['>', $dateInfo['time']], 'holiday' => '0']);
        if($this->db->numRows() == 1){
            $data['next'] = $activestaff[0]['staffid'];
        }
        else{
            $lastMethod = 'last'.$type.'ID';
            $nextactive = $this->db->select(self::STAFFHOURSTABLE, [$dateInfo['day'] => ['>', $dateInfo['time']], 'holiday' => '0', 'staffid' => ['>', $this->$lastMethod()]], ['staffid'], ['staffid' => 'ASC']);
            if($nextactive['staffid']){
                $data['next'] = $nextactive['staffid'];
            }
            else{
                $data['next'] = $this->db->select(self::STAFFHOURSTABLE, [$dateInfo['day'] => ['>', $dateInfo['time']], 'holiday' => '0'], ['staffid'], ['staffid' => 'ASC'])['staffid'];
            }
        }
        return $data;
    }
    
    /**
     * Gets the Day and Time to search for the active saleTeam member 
     * @return array Returns and array of both 'day' and 'time' to get the next active staff member
     */
    protected function dayAndTime(){
        $dateInfo = [];
        $dateInfo['day'] = strtolower(date('l'));
        $dateInfo['time'] = date('H:i:s');
        
        $endtime = $this->db->select(self::STAFFHOURSTABLE, [], [$dateInfo['day']], [$dateInfo['day'] => 'DESC'])[$dateInfo['day']];
        if($dateInfo['time'] > $endtime){
            $dateInfo['day'] = strtolower($this->dayNo((date("N")+1)));
            $dateInfo['time'] = "01:00:00";
            $endtime = $this->db->select(self::STAFFHOURSTABLE, [], [$dateInfo['day']], [$dateInfo['day'] => 'DESC'])[$dateInfo['day']];
        }

        if(($dateInfo['day'] == "Saturday" && $dateInfo['time'] > $endtime) || ($dateInfo['day'] == "Sunday")){
            $dateInfo['day'] = strtolower("Monday");
            $dateInfo['time'] = "01:00:00";
        }
        return $dateInfo;
    }
    
    /**
     * Returns the correct day to get the active staff member
     * @param int $num The number of the day to search
     * @return string returns the day name
     */
    protected function dayNo($num){
        if($num == 2){return "Tuesday";}
        elseif($num == 3){return "Wednesday";}
        elseif($num == 4){return "Thursday";}
        elseif($num == 5){return "Friday";}
        elseif($num == 6){return "Saturday";}
        elseif($num == 7){return "Sunday";}
        else{return "Monday";}
    }
    
    /**
     * Lists all of the staff members in the staff table
     * @return array|boolean If staff members exist will return array else will return false
     */
    public function listStaff(){
        return $this->db->selectAll(self::STAFFTABLE, [], ['staffid', 'fullname']);
    }
    
    /**
     * Returns the staff member name for a given sales staff ID
     * @param int $staffID This should be the sales staff ID of the person you wish to get the first name for
     * @return string|boolean Returns the first name if the sales staff ID exists else returns false
     */
    public function getStaffName($staffID){
        $staff = $this->getStaffInfoByID($staffID);
        if(!empty($staff)){
            return $staff['fullname'];
        }
        return false;
    }
    
    /**
     * Returns the hours of a sales team member with the given ID
     * @param int $staffID This should be the sales staff ID of the person you wish to get the hours for
     * @return array|boolean Returns the hours in an array if the sales staff ID exists else returns false
     */
    public function getStaffHours($staffID){
        return $this->db->select(self::STAFFHOURSTABLE, ['staffid' => $staffID]);
    }
    
    /**
     * Returns the hours of everyone within the Staff hours database
     * @return array|boolean Returns array of all of the time information if there is anything else return false
     */
    public function viewHours(){
        $hours = $this->db->selectAll(self::STAFFHOURSTABLE);
        if(!empty($hours)){
            foreach($hours as $a => $hour){
                $hours[$a]['name'] = $this->getStaffName($hour['staffid']);
            }
            return $hours;
        }
        return false;
    }
    
    /**
     * Updates the hours of the sales team member with the given ID
     * @param int $staffID This should be the Sales Staff ID of the person you are updating
     * @param null|string $monday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param null|string $tuesday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param null|string $wednesday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param null|string $thursday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param null|string $friday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param null|string $saturday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param null|string $sunday This should be the time the member finishes on that day if they are working. If its their day off should be null
     * @param int $holiday If the team member is on holiday should be set to 1 else should be 0
     * @return boolean If successfully updated returns true else returns false
     */
    public function updateHours($staffID, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $holiday = 0){
        return $this->db->update(self::STAFFHOURSTABLE, ['monday' => $monday, 'tuesday' => $tuesday, 'wednesday' => $wednesday, 'thursday' => $thursday, 'friday' => $friday, 'saturday' => $saturday, 'sunday' => $sunday, 'holiday' => $holiday], ['staffid' => $staffID]);
    }
    
    /**
     * Returns the ID of the sales team member for the given field name
     * @param string $field This should be the field name you are searching for the last team member used
     * @return int This will be the sales staff ID
     */
    protected function getLastID($field){
        $last = $this->db->select(self::STAFFTABLE, [$field => 1], ['staffid']);
        return $last['staffid'];
    }
    
    /**
     * Updates the users to specify which user was last assigned
     * @param int $current This should be the current user that was last assigned
     * @param string $field The field that you are searching on for last assigned
     * @return boolean If the information is updated will return true else return false
     */
    protected function updateLastUser($current, $field){
        $this->db->update(self::STAFFTABLE, [$field => 0]);
        return $this->db->update(self::STAFFTABLE, [$field => 1], ['staffid' => $current]);
    }
    
    /**
     * Magic method call so that any number of methods can be called to check and update for last assigned
     * @param string $name This should be the name of the method being called g.g lastSale(), lastTour(), etc or updateLastSale() or updateLastTour() 
     * @param array $arguments Is should be any arguments being given to the methods e.g. updateLastSale(array($userID)) or updateLastSale(array(3));
     * @return int|boolean If is a ching function will return the last user's assigned ID else if is an update method will return true or false if the database is updated or not 
     */
    public function __call($name, $arguments){
        $field = preg_replace("/[^a-zA-Z0-9]/", "", $name);
        if(substr($name, 0, 4) === 'last'){
            return $this->getLastID(strtolower(substr($field, 4, -2)));
        }
        elseif(substr($name, 0, 10) === 'updateLast'){
            return $this->updateLastUser(intval($arguments[0]), strtolower(substr($field, 10)));
        }
    }
}

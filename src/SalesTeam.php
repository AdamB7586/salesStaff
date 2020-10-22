<?php
namespace Staff;

use DBAL\Database;

class SalesTeam
{
    protected $db;
    protected $staffinfo;
    
    public $StaffTable = 'sales_staff';
    public $StaffHoursTable = 'sales_staff_hours';
    
    public $days = [1 => 'monday', 2 => "tuesday", 3 => "wednesday", 4 => "thursday", 5 => "friday", 6 => "saturday", 7 => "sunday"];
    public $staffDefault = ['id' => 99, 'staffid' => 99, 'fullname' => 'Default Name', 'firstname' => 'Default', 'email' => 'staff.email@example.com'];
    
    /**
     * This class is used for use with the sales team members
     * @param Database $db Add an instance of the database connection class
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    /**
     * Returns the number of sales staff in the database
     * @return int Returns the number of sales staff
     */
    public function numStaff()
    {
        return $this->db->count($this->StaffTable, ['active' => 1]);
    }
    
    /**
     * Gets the next active staff member
     * @param string $type Should be set to either 'enquiry' or 'sale' the default is 'sale'
     * @return array Returns the Staff members information as an array includes 'fullname', 'firstname', 'email' and 'staffid'
     */
    public function getActiveStaff($type = 'sale')
    {
        if ($this->numStaff() >= 1) {
            $activestaff = $this->nextActiveStaff($type);
            $updateName = 'updateLast'.$type;
            if ($activestaff['next'] >= 1) {
                $staff = $this->getStaffInfo(['active' => 1, 'staffid' => $activestaff['next']]);
                $this->$updateName($staff['id']);
                return $staff;
            }
            $staff = $this->getStaffInfo(['active' => 1, 'staffid' => ['>', $this->{'last'.$type.'ID'}]], ['staffid' => 'ASC']);
            if ($staff['id'] != $this->staffDefault['id']) { // Not the last one so do the next
                $this->$updateName($staff['id']);
                return $staff;
            }
            $newStaff = $this->getStaffInfo(['active' => 1], ['staffid' => 'ASC']); // Last one so start from beginning
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
    public function getStaffInfoByID($id)
    {
        if (is_numeric($id)) {
            return $this->getStaffInfo(['staffid' => $id]);
        }
        return false;
    }
    
    /**
     * Returns the staff information for the given variables
     * @param array $where Should be in the form of a where query e.g. array('active' => '1', etc)
     * @param array $order Should be in the form of a order query e.g. array('staffid' => 'ASC')
     * @return array Returns the Staff members information as an array includes 'id', 'fullname', 'firstname', 'email' and 'staffid'
     */
    protected function getStaffInfo($where = [], $order = [])
    {
        $staff = $this->db->select($this->StaffTable, $where, '*', $order);
        if (isset($staff['staffid'])) {
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
    public function nextActiveStaff($type)
    {
        $data = [];
        $activestaff = $this->db->query("SELECT `{$this->StaffHoursTable}`.`staffid` FROM `{$this->StaffHoursTable}`, `{$this->StaffTable}` WHERE `{$this->StaffTable}`.`active` = 1 AND `{$this->StaffTable}`.`staffid` = `{$this->StaffHoursTable}`.`staffid` AND `{$this->StaffHoursTable}`.`holiday` = 0;");
        if (count($activestaff) == 1) {
            $data['next'] = $activestaff[0]['staffid'];
        } else {
            $dateInfo = $this->dayAndTime(strtolower(date('l')), date('H:i:s'));
            $lastMethod = 'last'.$type.'ID';
            $nextactive = $this->db->query("SELECT `{$this->StaffHoursTable}`.`staffid` FROM `{$this->StaffHoursTable}`, `{$this->StaffTable}` WHERE `{$this->StaffTable}`.`active` = 1 AND `{$this->StaffTable}`.`staffid` = `{$this->StaffHoursTable}`.`staffid` AND `{$this->StaffHoursTable}`.`{$dateInfo['day']}` > ? AND `{$this->StaffHoursTable}`.`holiday` = 0 AND `{$this->StaffHoursTable}`.`staffid` > ? ORDER BY `{$this->StaffHoursTable}`.`staffid` ASC;", [$dateInfo['time'], $this->$lastMethod()]);
            if (isset($nextactive[0]['staffid'])) {
                $data['next'] = $nextactive[0]['staffid'];
            } else {
                $data['next'] = $this->db->query("SELECT `{$this->StaffHoursTable}`.`staffid` FROM `{$this->StaffHoursTable}`, `{$this->StaffTable}` WHERE `{$this->StaffTable}`.`active` = 1 AND `{$this->StaffTable}`.`staffid` = `{$this->StaffHoursTable}`.`staffid` AND `{$this->StaffHoursTable}`.`{$dateInfo['day']}` > ? AND `{$this->StaffHoursTable}`.`holiday` = 0 ORDER BY `{$this->StaffHoursTable}`.`staffid` ASC LIMIT 1;", [$dateInfo['time']])[0]['staffid'];
            }
        }
        return $data;
    }
    
    /**
     * Gets the Day and Time to search for the active saleTeam member
     * @param string $day The day you are checking e.g 'monday', or 'saturday'
     * @param string $time The current time of the day you are checking if anyone is active after
     * @return array Returns and array of both 'day' and 'time' to get the next active staff member
     */
    public function dayAndTime($day, $time)
    {
        $dateInfo = [];
        $endtime = $this->db->query("SELECT `{$this->StaffHoursTable}`.`{$day}` FROM `{$this->StaffHoursTable}`, `{$this->StaffTable}` WHERE `{$this->StaffTable}`.`active` = 1 AND `{$this->StaffHoursTable}`.`holiday` = 0 ORDER BY `{$day}` DESC LIMIT 1;");
        if (!isset($endtime[0][$day]) || $time > $endtime[0][$day]) {
            return $this->dayAndTime($this->getDay($this->getDayNo($day) + 1), "00:00:01");
        } else {
            $dateInfo['day'] = $day;
            $dateInfo['time'] = $time;
        }
        return $dateInfo;
    }


    /**
     * Returns the correct day to get the active staff member
     * @param int $num The number of the day to search
     * @return string returns the day name
     */
    protected function getDay($num)
    {
        if (array_key_exists($num, $this->days)) {
            return $this->days[$num];
        }
        return $this->days[1];
    }
    
    /**
     * Returns the day for the given day no
     * @param string $day This should be the day of the week
     * @return int Returns the allocated day no
     */
    protected function getDayNo($day)
    {
        $dayNo = array_search($day, $this->days);
        if (!$dayNo || $dayNo > 6) {
            return 0;
        }
        return $dayNo;
    }
    
    /**
     * Lists all of the staff members in the staff table
     * @return array|boolean If staff members exist will return array else will return false
     */
    public function listStaff()
    {
        return $this->db->selectAll($this->StaffTable, [], ['staffid', 'fullname']);
    }
    
    /**
     * Returns the staff member name for a given sales staff ID
     * @param int $staffID This should be the sales staff ID of the person you wish to get the first name for
     * @return string|boolean Returns the first name if the sales staff ID exists else returns false
     */
    public function getStaffName($staffID)
    {
        $staff = $this->getStaffInfoByID($staffID);
        if (!empty($staff)) {
            return $staff['fullname'];
        }
        return false;
    }
    
    /**
     * Returns the hours of a sales team member with the given ID
     * @param int $staffID This should be the sales staff ID of the person you wish to get the hours for
     * @return array|boolean Returns the hours in an array if the sales staff ID exists else returns false
     */
    public function getStaffHours($staffID)
    {
        return $this->db->select($this->StaffHoursTable, ['staffid' => $staffID]);
    }
    
    /**
     * Returns the hours of everyone within the Staff hours database
     * @return array|boolean Returns array of all of the time information if there is anything else return false
     */
    public function viewHours()
    {
        $hours = $this->db->selectAll($this->StaffHoursTable);
        if (!empty($hours)) {
            foreach ($hours as $a => $hour) {
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
    public function updateHours($staffID, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday, $holiday = 0)
    {
        return $this->db->update($this->StaffHoursTable, ['monday' => $monday, 'tuesday' => $tuesday, 'wednesday' => $wednesday, 'thursday' => $thursday, 'friday' => $friday, 'saturday' => $saturday, 'sunday' => $sunday, 'holiday' => $holiday], ['staffid' => $staffID]);
    }
    
    /**
     * Returns the ID of the sales team member for the given field name
     * @param string $field This should be the field name you are searching for the last team member used
     * @return int This will be the sales staff ID
     */
    protected function getLastID($field)
    {
        $last = $this->db->select($this->StaffTable, [$field => 1], ['staffid']);
        if(is_array($last)){
            return $last['staffid'];
        }
        return 0;
    }
    
    /**
     * Updates the users to specify which user was last assigned
     * @param int $current This should be the current user that was last assigned
     * @param string $field The field that you are searching on for last assigned
     * @return boolean If the information is updated will return true else return false
     */
    protected function updateLastUser($current, $field)
    {
        $this->db->update($this->StaffTable, [$field => 0]);
        return $this->db->update($this->StaffTable, [$field => 1], ['staffid' => $current]);
    }
    
    /**
     * Magic method call so that any number of methods can be called to check and update for last assigned
     * @param string $name This should be the name of the method being called g.g lastSale(), lastTour(), etc or updateLastSale() or updateLastTour()
     * @param array $arguments Is should be any arguments being given to the methods e.g. updateLastSale(array($userID)) or updateLastSale(array(3));
     * @return int|boolean If is a ching function will return the last user's assigned ID else if is an update method will return true or false if the database is updated or not
     */
    public function __call($name, $arguments)
    {
        $field = preg_replace("/[^a-zA-Z0-9]/", "", $name);
        if (substr($name, 0, 4) === 'last') {
            return $this->getLastID(strtolower(substr($field, 4, -2)));
        } elseif (substr($name, 0, 10) === 'updateLast') {
            return $this->updateLastUser(intval($arguments[0]), strtolower(substr($field, 10)));
        }
    }
}

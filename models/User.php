<?php
namespace app\models;

use app\framework\Model;

class User extends Model {
    /** varchar(30) */
    protected $username;
    
    /** varchar(250) */
    protected $password_hash;
    
    /** tinyint */
    protected $suspended;
    
    /** enum('admin', 'user', 'superadmin', 'a') */
    protected $user_type = "a";
    
    /** User */
    protected $created_by;
    
    /** datetime */
    protected $created_at;
    
    /** json */
    protected $data = '[null]';
    
    /** file */
    protected $profile_pic;
    
    protected function beforeSave() {
        if ($this->isNewRecord) {
            $this->created_at = date("Y-m-d H:i:s");
        }
    }
}

?>
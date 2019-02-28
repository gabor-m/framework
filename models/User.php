<?php
namespace app\models;

class User extends Model {
    /** varchar(30) */
    public $username;
    
    /** varchar(250) */
    public $password_hash;
    
    /** tinyint */
    public $suspended;
    
    /** enum('admin', 'user', 'superadmin', 'a') */
    public $user_type = "a";
    
    /** User */
    public $created_by;
    
    /** datetime */
    public $created_at;
    
    public function beforeSave() {
        $this->created_at = date("Y-m-d H:i:s");
    }
}

?>
<?php
namespace app\models;

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
    
    protected function beforeSave() {
        $this->created_at = date("Y-m-d H:i:s");
    }
}

?>
<?php

namespace App\Models;
use CodeIgniter\Model;

class UsersModel extends Model {
    protected $table = 'users';
    protected $allowedFields = ['name', 'phone', 'created_at'];

    public function getUsers(){
        return $this->findAll();
    }

    public function getUserById($id){
        return $this->find($id);
    }

    public function updateUser($id, $data){
        return $this->update($id, $data);
    }

    public function deleteUser($id){
        return $this->delete($id);
    }

    public function createUser($data){
        return $this->insert($data);
    }
    
}
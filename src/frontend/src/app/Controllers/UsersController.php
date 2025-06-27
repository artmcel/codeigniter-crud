<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UsersModel;

class UsersController extends BaseController {

    public function index(){
        $usersModel = model(UsersModel::class);
        $data['users'] = $usersModel->getUsers();
        return view('users', $data);
    }

    public function create(){
        if ($this->request->getMethod() == 'POST') {
            $usersModel = model(UsersModel::class);
            $data = [
                'name' => $this->request->getPost('name'),
                'phone' => $this->request->getPost('phone'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $usersModel->createUser($data);
            return redirect()->to(base_url('users'));
        }
        return view('create_user');
    }

    public function edit($id){
        $usersModel = model(UsersModel::class);
        if ($this->request->getMethod() === 'POST') {
            $data = [
                'name' => $this->request->getPost('name'),
                'phone' => $this->request->getPost('phone')
            ];
            $usersModel->updateUser($id, $data);
            return redirect()->to(base_url('users'));
        }
        $data['user'] = $usersModel->getUserById($id);
        return view('edit_user', $data);
    }

    public function delete($id){
        $usersModel = model(UsersModel::class);
        $usersModel->deleteUser($id);
        return redirect()->to(base_url('users'));
    }
}
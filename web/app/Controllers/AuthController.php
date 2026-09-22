<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use App\Models\AppUserModel;

class AuthController extends BaseController
{
    use ResponseTrait;

    public function login(){
        $mod_user = new AppUserModel();
        //$this->session = session();
        $session = \Config\Services::session();

        $dated = date('Y-m-d H:i:s');

        if ($this->request->getPost()){

            $lg_eml = $this->request->getVar('varEmail');
            $lg_pwd = $this->request->getVar('varPassword');

            $data = $mod_user->where('user_Email', $lg_eml)->first();

            if($data){
                $pass = $data['user_Password'];
                $verify_pass = password_verify($lg_pwd, $pass);
                if($verify_pass){
                    $ses_data = [
                        'user_id'       => $data['user_Id'],
                        'user_name'     => $data['user_Name'],
                        'user_email'    => $data['user_Email'],
                        'logged_in'     => TRUE
                    ];
                    $this->session->set($ses_data);
                    return $this->respond([
                        'status' => 1,
                        'message' => "Logged Successfully",
                        'time' => $dated,
                        'uuid' => $data['user_Code'],
                        'userid' => $data['user_Id'],
                        'user_name' => $data['user_Name'],
                        'user_email' => $data['user_Email'],
                    ]);
                }else{
                    //$session->setFlashdata('msg', 'Wrong Password');
                    return $this->respond([
                        'status' => 0,
                        'message' => "Wrong credentials",
                        'time' => $dated,
                        'userid' => "null"
                    ]);
                }
            }else{
                //$session->setFlashdata('msg', 'Email not Found');
                return $this->respond([
                    'status' => 0,
                    'message' => "No such user",
                    'time' => $dated,
                    'userid' => "null"
                ]);
            }
        }else{
            return $this->respond([
                'status' => 2,
                'message' => "Unexpected request sent",
                'time' => $dated
            ]);
        }
    }

    public function register(){
        $mod_user = new AppUserModel();
        //helper(['form']);

        $dated = date('Y-m-d H:i:s');
        if ($this->request->getPost()){

            $rules = [
                'varUsername'=> 'required|min_length[5]|max_length[20]',
                'varEmail'=> 'required|min_length[8]|max_length[30]|valid_email|is_unique[tbl_users.user_Email]',
                'varPassword'=> 'required|min_length[3]|max_length[20]'
            ];

            if($this->validate($rules)){
                $uuid = random_string('alnum', 16);
                $data = [
                    'user_Name' => $this->request->getVar('varUsername'),
                    'user_Email' => $this->request->getVar('varEmail'),
                    'user_Code' => $uuid,
                    'user_Password' => password_hash($this->request->getVar('varPassword'), PASSWORD_DEFAULT),
                    'user_Created' => $dated,
                    //'user_Created' => time(),
                ];
                $pushed = $mod_user->user_register($data);
                if ($pushed){
                    $user_id = $mod_user->user_last_created();
                    return $this->respond([
                        'status' => 1,
                        'message' => "User Added to Database",
                        'time' => $dated,
                        'userid' => $user_id,
                        'uuid' => $uuid,
                    ]);
                }else{
                    return $this->respond([
                        'status' => 0,
                        'message' => "Unknown Error, Please try again",
                        'time' => $dated,
                        'userid' => "Null"
                    ]);
                }

            }else{
                $validation = $this->validator;
                return $this->respond([
                    'status' => 0,
                    'time' => $dated,
                    'message' => trim(strip_tags($validation->listErrors()))
                ]);
            }

        }else{
            return $this->respond([
                'status' => 2,
                'time' => $dated,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function user_info(){
        $mod_user = new AppUserModel();
        $dated = date('Y-m-d H:i:s');

        if ($this->request->getPost()){
            $u_id = $this->request->getVar('varId');

            \App\Libraries\Audit::log(
                'get/user_info',
                'data',
                'User info requested',
                [
                    'var_user_id' => $u_id,
                    'app_version' => $this->request->getHeaderLine('X-App-Version') ?: null,
                ],
                null
            );

            $pushed = $mod_user->user_info($u_id);
            if ($pushed){
                return $this->respond([
                    'status' => 1,
                    'time' => $dated,
                    'message' => $pushed
                ]);
            }else{
                return $this->respond([
                    'status' => 0,
                    'time' => $dated,
                    'message' => "No Such user Added to Database"
                ]);
            }
        }else{
            return $this->respond([
                'status' => 2,
                'time' => $dated,
                'message' => "Unexpected request sent"
            ]);
        }
    }

    public function user_logout(){
        $session = session();
        $session->destroy();
        return redirect()->to('/auth/login');
    }
}

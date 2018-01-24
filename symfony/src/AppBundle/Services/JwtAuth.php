<?php
namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{
    public $manager;

    public function __construct($manager) {
        $this->manager = $manager;
    }

    public function signup($email,$password){
        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(array(
            "email" => $email,
            "password" => $password
            ));
        
        $signup = false;
        if(is_object($user)){
            $signup = true;
        }
        
        if($signup == true){
            //GENERAR TOKEN JWT
            
            $data = array(
                "status" => 'success',
                "user" => $user
                );
        }else{
            $data = array(
                "status" => 'error',
                "user" => 'Login fallido'
                );
        }
        
        return $data;
    }
}


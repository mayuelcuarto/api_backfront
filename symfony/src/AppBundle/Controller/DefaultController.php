<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
    
    public function loginAction(Request $request){
        $helpers = $this->get(Helpers::class);
        
        // Recibir json por POST
        $json = $request->get('json', null);
        
        //Array a devolver por defecto
        $data = array(
            'status' => 'error',
            'data' => 'Send json via POST'
        );
        
        if($json != null){
            // Me haces el login
            
            //Convertimos un json a un objeto de PHP
            $params = json_decode($json);
            
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;
            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Este email no es válido";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            //Cifrar password
            $pwd = hash('sha256', $password);
            
            if(count($validate_email) == 0 && $password != null){
                
                $jwt_auth = $this->get(JwtAuth::class);
                
                if($getHash == null || $getHash == false){
                    $signup = $jwt_auth->signup($email,$pwd);
                }else{
                    $signup = $jwt_auth->signup($email,$pwd,true);
                }
                
                return $this->json($signup);
            }else{
                $data = array(
                    'status' => 'Success',
                    'data'   => 'Email o Password incorrecto'
                );
            }    
        }
        return $helpers->json($data) ;
    }
    
    public function pruebasAction(Request $request) {
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        $token = $request->get('authorization',null);
        
        if($token && $jwt_auth->checkToken($token) == true){
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('BackendBundle:User');
            $users = $userRepo->findAll();

            return $helpers->json(array(
                    'status' => 'Success',
                    'users'  => $users
                    ));
        }else{
            return $helpers->json(array(
                    'status' => 'Error',
                    'code'   => 400,
                    'users'  => 'Autorización no válida'
                    ));
        }
        
        /*
        die();
        
        return new JsonResponse(array(
                'status' => 'Success',
                'users' => $users[0]->getName()
                ));
         * 
         */
    }
}

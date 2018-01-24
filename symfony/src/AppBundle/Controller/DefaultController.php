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
            
            $emailConstraint = new Assert\Email();
            $emailConstraint->message = "Este email no es válido";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            if(count($validate_email) == 0 && $password != null){
                
                $jwt_auth = $this->get(JwtAuth::class);
                
                $signup = $jwt_auth->signup($email,$password);
                
                $data = array(
                    'status' => 'success',
                    'data' => 'Login exitoso',
                    'signup' => $signup
                );
            }else{
                $data = array(
                    'status' => 'success',
                    'data' => 'Email o Password incorrecto'
                );
            }    
        }
        return $helpers->json($data) ;
    }
    
    public function pruebasAction() {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('BackendBundle:User');
        $users = $userRepo->findAll();
        
        $helpers = $this->get(Helpers::class);
        return $helpers->json(array(
                'status' => 'Success',
                'users' => $users
                ));
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

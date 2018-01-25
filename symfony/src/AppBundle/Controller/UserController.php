<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use AppBundle\Services\Helpers;

class UserController extends Controller{
    public function newAction(Request $request){
        $helpers = $this->get(Helpers::class);
        
        $json = $request->get('json',null);
        $params = json_decode($json);
        
        $data = array(
            "status"  => "Error",
            "code"    => 400,
            "msg" => "Usuario no creado !!"
        );
        
        if($json != null){
            $createdAt = new \DateTime("now");
            $role = "user";
            
            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name)) ? $params->name : null;
            $surname = (isset($params->surname)) ? $params->surname : null;
            $password = (isset($params->password)) ? $params->password : null;
            
            $emailConstraint = new Assert\Email;
            $emailConstraint->message = "Este email no es válido!!";
            $validate_email = $this->get("validator")->validate($email, $emailConstraint);
            
            if($email != null && count($validate_email) == 0 && $password != null && $name != null & $surname != null){
                $user = new User();
                $user->setCreatedAt($createdAt);
                $user->setRole($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);
                
                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                    "email" => $email
                ));
                
                if(count($isset_user) == 0){
                    $em->persist($user);
                    $em->flush();
                    
                    $data = array(
                        "status"  => "Success",
                        "code"    => 200,
                        "msg" => "Nuevo Usuario creado !!",
                        "user" => $user
                    );
                }else{
                    $data = array(
                        "status"  => "Error",
                        "code"    => 400,
                        "msg" => "Usuario no creado, duplicado !!"
                    );
                }
            }
        }
        
        return $helpers->json($data);
    }
}
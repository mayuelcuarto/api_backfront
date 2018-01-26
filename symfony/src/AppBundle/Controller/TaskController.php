<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller{
    public function newAction(Request $request, $id = null){
        $helpers = $this->get(Helpers::class);
        $jwt_auth = $this->get(JwtAuth::class);
        
        $token = $request->get('authorization',null);
        $authCheck = $jwt_auth->checkToken($token);
        
        if($authCheck==true){
            $identity = $jwt_auth->checkToken($token, true);
            $json = $request->get('json', null);
            
            if($json != null){
                $params = json_decode($json);
                
                $createdAt = new \DateTime('now');
                $updatedAt = new \DateTime('now');
                
                $user_id = ($identity->sub) ? $identity->sub : null;
                $title = (isset($params->title)) ? $params->title : null;
                $description = (isset($params->description)) ? $params->description : null;
                $status = (isset($params->status)) ? $params->status : null;
                
                if($user_id != null && $title != null){
                    $em = $this->getDoctrine()->getManager();
                    
                    $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                       "id" => $user_id 
                    ));
                    
                    if($id == null){
                        $task = new Task();
                        $task->setUser($user);
                        $task->setTitle($title);
                        $task->setDescription($description);
                        $task->setStatus($status);
                        $task->setCreatedAt($createdAt);
                        $task->setUpdatedAt($updatedAt);

                        $em->persist($task);
                        $em->flush();

                        $data = array(
                            "status" => "Success",
                            "code" => 200,
                            "data" => $task
                        );
                    }else{
                        $task = $em->getRepository('BackendBundle:Task')->findOneBy(array(
                               "id" => $id
                            ));
                        if(isset($identity->sub) && $identity->sub == $task->getUser()->getId()){ 
                            $task->setTitle($title);
                            $task->setDescription($description);
                            $task->setStatus($status);
                            $task->setUpdatedAt($updatedAt);

                            $em->persist($task);
                            $em->flush();

                            $data = array(
                                "status" => "Success",
                                "code" => 200,
                                "msg" => "Tarea actualizada",
                                "data" => $task
                            );
                        }else{
                            $data = array(
                            "status" => "Error",
                            "code" => 400,
                            "msg" => "Tarea no actualizada, no eres el dueño!!"
                            );
                        }
                    } 
                }else{
                    $data = array(
                        "status" => "Error",
                        "code" => 400,
                        "msg" => "Tarea no creada, validación fallida!!"
                    );
                }
            }else{
                $data = array(
                    "status" => "Error",
                    "code" => 400,
                    "msg" => "Tarea no creada, parámetros fallidos!!"
                );
            } 
        }else{
            $data = array(
                "status" => "Error",
                "code" => 400,
                "msg" => "Autorización no Válida!!"
            );
        }
        
        return $helpers->json($data);
    }
}
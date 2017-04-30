<?php
/**
 * Created by PhpStorm.
 * User: Erik
 * Date: 19/04/2017
 * Time: 18:56
 */

namespace SilexApp\Controller;


use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController
{
    public function getAction(Application $app, $id)
    {
        $sql = "SELECT * FROM user WHERE id = ?";
        $user = $app['db']->fetchAssoc($sql, array((int)$id)); //llamando al servicio
        $response = new Response();
        if (!$user) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', [
                    'message' => 'User not found'
                ]
            );
        } else {
            $response->setStatusCode(Response::HTTP_OK);
            $content = $app['twig']->render('showUser.twig', [
                    'user' => $user,
                    'app' => [
                        'name' => $app['app.name']
                    ]
                ]
            );
        }
        $response->setContent($content);
        return $response;
    }

    public function postAction(Application $app, Request $request){
        $response = new Response();
        if($request->isMethod('POST')){
            //Validate
            $name = $request->get('name');
            $email = $request->get('email');
            try{
                $app['db']->insert('user',[
                    'name' => $name,
                    'email' => $email
                ]
                );
                $lastInsertedId = $app['db']->fetchAssoc('SELECT id FROM user ORDER BY id DESC LIMIT 1');
                $id = $lastInsertedId['id'];
                $url = 'users/get/'.$id;
                return new RedirectResponse($url);
            }catch (Exception $e){
                $response->setStatusCode(Response::HTTP_BAD_REQUEST);
                $content = $app['twig']->render('addUser.twig',[
                    'errors' =>[
                        'unexpected' => 'An error has ocurred, pleasse try it again later'
                    ]
                ]);
                $response->setContent($content);
                return $response;
            }
        }
        $content = $app['twig']->render('addUser.twig', array('app' => [
            'name' => $app['app.name']
        ]));
        $response->setContent($content);
        return $response;
    }
}
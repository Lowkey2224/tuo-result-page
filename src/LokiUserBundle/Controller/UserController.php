<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 18.11.16
 * Time: 11:33
 */

namespace LokiUserBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserController
 * @package LokiUserBundle\Controller
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", methods={"GET"}, name="loki.user.user.index")
     */
    public function indexAction()
    {
        $userRepo = $this->getDoctrine()->getRepository('LokiUserBundle:User');
        $users = $userRepo->findBy([], ['enabled'=> 'ASC', 'username' => 'ASC']);
        return $this->render('LokiUserBundle:User:index.html.twig', [
            'users' => $users,
        ]);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 18.11.16
 * Time: 11:33
 */

namespace LokiUserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("has_role( 'ROLE_MODERATOR')")
     */
    public function indexAction()
    {
        $userRepo = $this->getDoctrine()->getRepository('LokiUserBundle:User');
        $users = $userRepo->findBy([], ['enabled' => 'ASC', 'username' => 'ASC']);
        return $this->render('LokiUserBundle:User:index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{userId}/promote",
     *     name="loki.user.promote",
     *     requirements={"userId":"\d+"}
     *     )
     * @Security("is_granted('delete', user)")
     */
    public function promoteAction($userId)
    {
        $userRepo = $this->getDoctrine()->getRepository('LokiUserBundle:User');
        $user = $userRepo->find($userId);
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->addRole($user->getUsername(), 'ROLE_MODERATOR');

        return $this->redirect($this->generateUrl('loki.user.user.index'));
    }

    /**
     * @param $userId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{userId}/demote",
     *     name="loki.user.demote",
     *     requirements={"userId":"\d+"}
     *     )
     * @Security("is_granted('delete', user)")
     */
    public function demoteAction($userId)
    {
        $userRepo = $this->getDoctrine()->getRepository('LokiUserBundle:User');
        $user = $userRepo->find($userId);
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->removeRole($user->getUsername(), 'ROLE_MODERATOR');
        return $this->redirect($this->generateUrl('loki.user.user.index'));
    }
}

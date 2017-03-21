<?php
/**
 * Created by PhpStorm.
 * User: jenz
 * Date: 18.11.16
 * Time: 11:33.
 */

namespace LokiUserBundle\Controller;

use LokiUserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class UserController.
 *
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
        $users    = $userRepo->findBy([], ['enabled' => 'ASC', 'username' => 'ASC']);

        return $this->render('LokiUserBundle:User:index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/promote",
     *     name="loki.user.promote",
     *     requirements={"userId":"\d+"}
     *     )
     * @Security("is_granted('delete', user)")
     */
    public function promoteAction(User $user)
    {
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->addRole($user->getUsername(), 'ROLE_MODERATOR');

        return $this->redirect($this->generateUrl('loki.user.user.index'));
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/demote",
     *     name="loki.user.demote",
     *     requirements={"userId":"\d+"}
     *     )
     * @Security("is_granted('delete', user)")
     */
    public function demoteAction(User $user)
    {
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->removeRole($user->getUsername(), 'ROLE_MODERATOR');

        return $this->redirect($this->generateUrl('loki.user.user.index'));
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/activate",
     *     name="loki.user.activate",
     *     requirements={"userId":"\d+"}
     *     )
     * @Security("has_role( 'ROLE_MODERATOR')")
     */
    public function activateAction(User $user)
    {
        $this->get('fos_user.util.user_manipulator')->activate($user->getUsername());

        return $this->redirect($this->generateUrl('loki.user.user.index'));
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/{id}/deactivate",
     *     name="loki.user.deactivate",
     *     requirements={"userId":"\d+"}
     *     )
     * @Security("has_role( 'ROLE_MODERATOR')")
     */
    public function deactivateAction(User $user)
    {
        $this->get('fos_user.util.user_manipulator')->deactivate($user->getUsername());

        return $this->redirect($this->generateUrl('loki.user.user.index'));
    }
}

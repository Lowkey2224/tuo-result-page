<?php

namespace LokiTuoResultBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use LokiTuoResultBundle\Entity\Guild;
use LokiTuoResultBundle\Form\Type\GuildType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GuildController.
 *
 * @Route("/guild")
 */
class GuildController extends Controller
{
    /**
     * @Route("/", name="loki.tuo.guild.index")
     * @Template()
     */
    public function indexAction()
    {
        $guildRepo = $this->getDoctrine()->getRepository('LokiTuoResultBundle:Guild');

        return ['guilds' => $guildRepo->findAll()];
    }

    /**
     * @param Request $request
     * @param Guild   $guild
     *
     * @return array|RedirectResponse
     * @Route("/{id}/edit", name="loki.tuo.guild.edit", requirements={"id":"\d+"})
     * @Route("/new", name="loki.tuo.guild.new", defaults={"id":null})
     * @Template()
     */
    public function editAction(Request $request, Guild $guild = null)
    {
        $guild = $guild ?: new Guild();
        $this->denyAccessUnlessGranted('edit.guild', $guild);
        $form = $this->createForm(GuildType::class, $guild);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getDoctrine()->getManager()->persist($guild);
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('loki.tuo.guild.index');
            } catch (UniqueConstraintViolationException $exception) {
                $form->get('name')->addError(new FormError('This Guild already Exists!'));
            }
        }

        return [
            'guild' => $guild,
            'form'  => $form->createView(),
        ];
    }
}

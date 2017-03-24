<?php

namespace LokiTuoResultBundle\Controller;

use LokiTuoResultBundle\Entity\Guild;
use LokiTuoResultBundle\Form\GuildType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Template(vars={"guilds"})
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
     * @Template(vars={"post"})
     */
    public function editAction(Request $request, Guild $guild = null)
    {
        $guild = $guild ?: new Guild();
        $this->denyAccessUnlessGranted('edit', $guild);
        $form = $this->createForm(GuildType::class, $guild);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($guild);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('loki.tuo.guild.index');
        }

        return [
                'guild'  => $guild,
                'form'   => $form->createView(),
            ];
    }
}

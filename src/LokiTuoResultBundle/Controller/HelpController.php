<?php

namespace LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class HelpController
 * @package LokiTuoResultBundle\Controller
 * @Route("/help")
 */
class HelpController extends Controller
{
    /**
     * @Route("/tuCredentials", name="loki.tuo.help.tucred")
     * @Template()
     */
    public function tuCredentialsHelpAction()
    {
        $moderators = ["Loki", "BenIntent"];
        return [
            'moderators' => $moderators,
        ];
    }
}

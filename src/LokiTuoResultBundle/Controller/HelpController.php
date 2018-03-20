<?php

namespace App\LokiTuoResultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class HelpController
 * @package App\LokiTuoResultBundle\Controller
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
        $moderators = ["Loki", "BenIntent", "Tostaky","Here'sJohnny!","phildouble1u1"];
        return [
            'moderators' => $moderators,
        ];
    }
}

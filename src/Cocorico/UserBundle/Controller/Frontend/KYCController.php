<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class KYCController extends Controller
{
    /**
     * @Route("/kyc", name="cocorico_user_kyc")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function kycAction()
    {
        $violations = $this->get('validator')->validate(
            $this->getUser(),
            null,
            'CocoricoKYC'
        );

        if (count($violations) === 0) {
            return new Response('');
        }

        $form = $this->get('form.factory')->createNamed(
            '',
            'user_kyc',
            $this->getUser(),
            array(
                'method' => 'POST',
                'action' => $this->generateUrl('cocorico_user_kyc')
            )
        );

        $success = $this->get('cocorico_user.form.handler.contact')->process($form);

        if ($success === 1) {
            return new Response('');
        }

        return $this->render(
            '@CocoricoUser/Frontend/KYC/modal.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
}

<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Controller\Dashboard;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/balance")
 */
class BalanceController extends Controller
{
    /**
     * @Route("/view", name="cocorico_balance_dashboard_balance_view")
     * @Method({"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction()
    {
        $balanceMovements = $this->getDoctrine()
            ->getRepository('CocoricoBalanceBundle:BalanceMovement')
            ->findToViewForUser($this->getUser());

        return $this->render(
            'CocoricoBalanceBundle:Dashboard/Balance:view.html.twig',
            array(
                'balanceMovements' => $balanceMovements,
            )
        );
    }
}

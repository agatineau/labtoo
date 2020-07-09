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

use Cocorico\BalanceBundle\Exception\RecoverBankAccountException;
use Cocorico\BalanceBundle\Exception\RecoverCheckNotValidException;
use Cocorico\BalanceBundle\Exception\RecoverFormNotSubmitException;
use Cocorico\BalanceBundle\Exception\RecoverInsufficientAmountException;
use Cocorico\BalanceBundle\Exception\RecoverNotCheckException;
use Cocorico\BalanceBundle\Model\RecoverCheck;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/balance")
 */
class RecoverController extends Controller implements TranslationContainerInterface
{
    /**
     * @Route("/recover", name="cocorico_balance_dashboard_balance_recover")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverAction()
    {
        $recoverFormHandler = $this->get('cocorico_balance.form.handler.recover');

        $recoverForm = $this->createNewRecoverForm();

        try {
            $recoverFormHandler->process($recoverForm);

        } catch (RecoverInsufficientAmountException $e) {

            $this->addFlashMessage('info', $e->getMessage());

            return $this->redirectToRoute('cocorico_balance_dashboard_balance_view');

        } catch (RecoverNotCheckException $e) {

            $this->addFlashMessage('warning', $e->getMessage());

            return $this->redirectToRoute('cocorico_balance_dashboard_balance_recover_check');

        } catch (RecoverBankAccountException $e) {

            $this->addFlashMessage('error', $e->getMessage());

            return $this->redirectToRoute('cocorico_user_dashboard_profile_edit_bank_account');

        } catch (RecoverFormNotSubmitException $e) {

            return $this->render(
                'CocoricoBalanceBundle:Dashboard/Balance:recover.html.twig',
                array(
                    'form' => $recoverForm->createView(),
                )
            );
        }

        $this->addFlashMessage('success', 'recover_success');

        return $this->redirectToRoute('cocorico_balance_dashboard_balance_view');
    }

    /**
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createNewRecoverForm()
    {
        return $this->get('form.factory')->createNamed(
            'recover',
            'recover',
            null,
            array(
                'action' => $this->generateUrl('cocorico_balance_dashboard_balance_recover'),
                'method' => 'POST',
            )
        );
    }

    /**
     * @Route("/recover-check", name="cocorico_balance_dashboard_balance_recover_check")
     * @Method({"GET", "POST"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function recoverCheckAction()
    {
        $recoverCheckFormHandler = $this->get('cocorico_balance.form.handler.recover_check');

        if ($recoverCheckFormHandler->isRequired()) {

            $recoverCheck = new RecoverCheck();
            $recoverCheckForm = $this->createNewRecoverCheckForm($recoverCheck);

            try {
                $recoverCheckFormHandler->process($recoverCheckForm);

            } catch (RecoverCheckNotValidException $e) {

                return $this->render(
                    'CocoricoBalanceBundle:Dashboard/Balance:recover_check.html.twig',
                    array(
                        'form' => $recoverCheckForm->createView(),
                    )
                );
            }

            return $this->redirectToRoute('cocorico_balance_dashboard_balance_recover_check');
        }

        return $this->redirectToRoute('cocorico_balance_dashboard_balance_recover');
    }

    /**
     * @param RecoverCheck $recoverCheck
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createNewRecoverCheckForm(RecoverCheck $recoverCheck)
    {
        return $this->get('form.factory')->createNamed(
            'recover_check',
            'recover_check',
            $recoverCheck,
            array(
                'action' => $this->generateUrl('cocorico_balance_dashboard_balance_recover_check'),
                'method' => 'POST',
            )
        );
    }

    /**
     * @param string $type
     * @param string $message
     * @param string $domain
     * @param int|null $code
     */
    private function addFlashMessage(
        $type,
        $message,
        $domain = 'cocorico_balance',
        $code = null
    ) {
        /** @Ignore */
        $message = $this->get('translator')->trans($message, array(), $domain);
        if ($code) {
            $message = sprintf('%s (%d)', $message, $code);
        }
        $this->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message('recover_success', 'cocorico_balance'),
            new Message('credit_card_success', 'cocorico_balance'),
        );
    }
}

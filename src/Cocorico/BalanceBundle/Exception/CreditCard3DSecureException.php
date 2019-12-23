<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Exception;

class CreditCard3DSecureException extends \Exception
{
    /**
     * @var string
     */
    private $redirectUrl;


    /**
     * CreditCard3DSecureException constructor.
     * @param string $redirectUrl
     */
    public function __construct($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}

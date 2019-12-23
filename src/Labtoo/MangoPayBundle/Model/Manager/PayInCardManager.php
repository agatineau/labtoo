<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Labtoo\MangoPayBundle\Model\Manager;

use Labtoo\MangoPayBundle\Request\PayInCardRequest;
use MangoPay\PayIn;

class PayInCardManager
{
    /**
     * @var PayInCardRequest
     */
    protected $payInCardRequest;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @param PayInCardRequest $payInCardRequest
     * @param string $currency
     */
    public function __construct(
        PayInCardRequest $payInCardRequest,
        $currency
    )
    {
        $this->payInCardRequest = $payInCardRequest;
        $this->currency = $currency;
    }

    /**
     * @param $userId
     * @param $walletId
     * @param $amount
     * @param $cardId
     * @param $returnUrl
     * @param $tag
     * @return PayIn
     */
    public function create($userId, $walletId, $amount, $cardId, $returnUrl, $tag)
    {
        return $this->payInCardRequest->create(array(
            'AuthorId' => $userId,
            'CreditedWalletId' => $walletId,
            'Currency' => $this->currency,
            'DebitedFunds' => $amount,
            'CardId' => $cardId,
            'Fees' => 0,
            'SecureModeReturnURL' => $returnUrl,
            'Tag' => $tag
        ));
    }

    /**
     * @param $payInId
     * @return bool|\MangoPay\PayIn
     */
    public function get($payInId)
    {
        return $this->payInCardRequest->get($payInId);
    }
}

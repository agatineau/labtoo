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

use Labtoo\MangoPayBundle\Request\PayInBankWireRequest;
use MangoPay\PayIn;

class PayInBankWireManager
{
    /**
     * @var PayInBankWireRequest
     */
    protected $payInBankWireRequest;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @param PayInBankWireRequest $payInBankWireRequest
     * @param string $currency
     */
    public function __construct(
        PayInBankWireRequest $payInBankWireRequest,
        $currency
    )
    {
        $this->payInBankWireRequest = $payInBankWireRequest;
        $this->currency = $currency;
    }

    /**
     * @param $userId
     * @param $walletId
     * @param $amount
     * @param $tag
     * @return PayIn
     */
    public function create($userId, $walletId, $amount, $tag)
    {
        return $this->payInBankWireRequest->create(array(
            'AuthorId' => $userId,
            'CreditedWalletId' => $walletId,
            'Currency' => $this->currency,
            'DebitedFunds' => $amount,
            'Fees' => 0,
            'Tag' => $tag
        ));
    }

    /**
     * @param $payInId
     * @return bool|\MangoPay\PayIn
     */
    public function get($payInId)
    {
        return $this->payInBankWireRequest->get($payInId);
    }
}

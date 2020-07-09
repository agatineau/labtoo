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

use Labtoo\MangoPayBundle\Request\PayOutBankWireRequest;
use MangoPay\PayOut;

class PayOutBankWireManager
{
    /**
     * @var PayOutBankWireRequest
     */
    protected $payOutBankWireRequest;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @param PayOutBankWireRequest $payOutBankWireRequest
     * @param string $currency
     */
    public function __construct(
        PayOutBankWireRequest $payOutBankWireRequest,
        $currency
    )
    {
        $this->payOutBankWireRequest = $payOutBankWireRequest;
        $this->currency = $currency;
    }

    /**
     * @param $userId
     * @param $walletId
     * @param $bankAccountId
     * @param $amount
     * @param $tag
     * @return PayOut
     */
    public function create($userId, $walletId, $bankAccountId, $amount, $tag)
    {
        return $this->payOutBankWireRequest->create(array(
            'AuthorId' => $userId,
            'DebitedWalletId' => $walletId,
            'Currency' => $this->currency,
            'DebitedFunds' => $amount,
            'Fees' => 0,
            'BankAccountId' => $bankAccountId,
            'Tag' => $tag
        ));
    }

    /**
     * @param $payOutId
     * @return bool|\MangoPay\PayOut
     */
    public function get($payOutId)
    {
        return $this->payOutBankWireRequest->get($payOutId);
    }
}

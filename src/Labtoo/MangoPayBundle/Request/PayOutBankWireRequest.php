<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Labtoo\MangoPayBundle\Request;

use Cocorico\MangoPayBundle\Request\BaseRequest;
use MangoPay\Money;
use MangoPay\PayOut;
use MangoPay\PayOutPaymentDetailsBankWire;

class PayOutBankWireRequest extends BaseRequest
{
    /**
     * @var array
     */
    static $createRequiredFields = array(
        'AuthorId',
        'DebitedWalletId',
        'Currency',
        'DebitedFunds',
        'Fees',
        'BankAccountId',
        'Tag'
    );

    /**
     * @param array $parameters
     * @return PayOut
     */
    public function create(array $parameters)
    {
        $this->getLogger()->debug("PayOutBankWireRequest create call.");

        $parameters = $this->resolveOptions($parameters, self::$createRequiredFields);

        $payOut = new PayOut();
        $payOut->AuthorId = $parameters["AuthorId"];
        $payOut->DebitedWalletId = $parameters["DebitedWalletId"];
        $payOut->DebitedFunds = new Money();
        $payOut->DebitedFunds->Currency = $parameters["Currency"];
        $payOut->DebitedFunds->Amount = $parameters["DebitedFunds"];
        $payOut->Fees = new Money();
        $payOut->Fees->Currency = $parameters["Currency"];
        $payOut->Fees->Amount = $parameters["Fees"];
        $payOut->PaymentType = 'BANK_WIRE';
        $payOut->MeanOfPaymentDetails = new PayOutPaymentDetailsBankWire();
        $payOut->MeanOfPaymentDetails->BankAccountId = $parameters["BankAccountId"];
        $payOut->Tag = $parameters["Tag"];

        $payOut = $this->mangopay->api->PayOuts->Create($payOut);

        $this->getLogger()->debug(
            'PayOut BankWire creation:' .
            '|-Id:' . $payOut->Id .
            '|-AuthorId:' . $payOut->AuthorId .
            '|-DebitedWalletId:' . $payOut->DebitedWalletId .
            '|-DebitedFunds Currency:' . $payOut->DebitedFunds->Currency .
            '|-DebitedFunds:' . $payOut->DebitedFunds->Amount .
            '|-Fees Currency:' . $payOut->Fees->Currency .
            '|-Fees:' . $payOut->Fees->Amount .
            '|-Status:' . $payOut->Status .
            '|-ResultCode:' . $payOut->ResultCode .
            '|-ResultMessage:' . $payOut->ResultMessage
        );

        return $payOut;
    }

    /**
     * @param $payOutId
     * @return bool|PayOut
     */
    public function get($payOutId)
    {
        $payOut = $this->mangopay->api->PayOuts->Get($payOutId);

        if (!$payOut instanceof PayOut) return false;

        /** @var PayOutPaymentDetailsBankWire $paymentDetails */
        $paymentDetails = $payOut->MeanOfPaymentDetails;

        $this->getLogger()->debug(
            'PayOut BankWire fetching:' .
            '|-Id:' . $payOut->Id .
            '|-BankAccountId:' . $paymentDetails->BankAccountId .
            '|-Status:' . $payOut->Status .
            '|-ResultCode:' . $payOut->ResultCode .
            '|-ResultMessage:' . $payOut->ResultMessage
        );

        return $payOut;
    }
}

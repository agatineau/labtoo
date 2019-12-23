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
use MangoPay\BankAccountDetailsIBAN;
use MangoPay\Money;
use MangoPay\PayIn;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInPaymentDetailsBankWire;

class PayInBankWireRequest extends BaseRequest
{
    /**
     * @var array
     */
    static $createRequiredFields = array(
        'AuthorId',
        'CreditedWalletId',
        'Currency',
        'DebitedFunds',
        'Fees',
        'Tag'
    );

    /**
     * @param array $parameters
     * @return PayIn
     */
    public function create(array $parameters)
    {
        $this->getLogger()->debug("PayInBankWireRequest create call.");

        $parameters = $this->resolveOptions($parameters, self::$createRequiredFields);

        $payIn = new PayIn();
        $payIn->AuthorId = $parameters["AuthorId"];
        $payIn->CreditedWalletId = $parameters["CreditedWalletId"];
        $payIn->PaymentType = 'BANK_WIRE';
        $payIn->ExecutionType = 'DIRECT';
        $payIn->PaymentDetails = new PayInPaymentDetailsBankWire();
        $payIn->PaymentDetails->DeclaredDebitedFunds = new Money();
        $payIn->PaymentDetails->DeclaredDebitedFunds->Currency = $parameters["Currency"];
        $payIn->PaymentDetails->DeclaredDebitedFunds->Amount = $parameters["DebitedFunds"];
        $payIn->PaymentDetails->DeclaredFees = new Money();
        $payIn->PaymentDetails->DeclaredFees->Currency = $parameters["Currency"];
        $payIn->PaymentDetails->DeclaredFees->Amount = $parameters["Fees"];
        $payIn->ExecutionDetails = new PayInExecutionDetailsDirect();
        $payIn->Tag = $parameters["Tag"];

        $payIn = $this->mangopay->api->PayIns->Create($payIn);

        $this->getLogger()->debug(
            'PayIn BankWire creation:' .
            '|-Id:' . $payIn->Id .
            '|-AuthorId:' . $payIn->AuthorId .
            '|-CreditedWalletId:' . $payIn->CreditedWalletId .
            '|-Status:' . $payIn->Status .
            '|-ResultCode:' . $payIn->ResultCode .
            '|-ResultMessage:' . $payIn->ResultMessage
        );

        return $payIn;
    }

    /**
     * @param $payInId
     * @return bool|PayIn
     */
    public function get($payInId)
    {
        $payIn = $this->mangopay->api->PayIns->Get($payInId);

        if (!$payIn instanceof PayIn) return false;

        /** @var PayInPaymentDetailsBankWire $paymentDetails */
        $paymentDetails = $payIn->PaymentDetails;

        /** @var BankAccountDetailsIBAN $bankAccountDetails */
        $bankAccountDetails = $paymentDetails->BankAccount->Details;

        $this->getLogger()->debug(
            'PayIn BankWire fetching:' .
            '|-Id:' . $payIn->Id .
            '|-WireReference:' . $paymentDetails->WireReference .
            '|-IBAN:' . $bankAccountDetails->IBAN .
            '|-Status:' . $payIn->Status .
            '|-ResultCode:' . $payIn->ResultCode .
            '|-ResultMessage:' . $payIn->ResultMessage
        );

        return $payIn;
    }
}

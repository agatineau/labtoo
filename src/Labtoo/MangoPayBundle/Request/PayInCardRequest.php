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
use MangoPay\PayInPaymentDetailsCard;

class PayInCardRequest extends BaseRequest
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
        'CardId',
        'SecureModeReturnURL',
        'Tag'
    );

    /**
     * @param array $parameters
     * @return PayIn
     */
    public function create(array $parameters)
    {
        $this->getLogger()->debug("PayInCardRequest create call.");

        $parameters = $this->resolveOptions($parameters, self::$createRequiredFields);

        $payIn = new PayIn();
        $payIn->AuthorId = $parameters["AuthorId"];
        $payIn->CreditedWalletId = $parameters["CreditedWalletId"];
        $payIn->DebitedFunds = new Money();
        $payIn->DebitedFunds->Currency = $parameters["Currency"];
        $payIn->DebitedFunds->Amount = $parameters["DebitedFunds"];
        $payIn->Fees = new Money();
        $payIn->Fees->Currency = $parameters["Currency"];
        $payIn->Fees->Amount = $parameters["Fees"];
        $payIn->ExecutionType = 'DIRECT';
        $payIn->PaymentType = 'CARD';
        $payIn->PaymentDetails = new PayInPaymentDetailsCard();
        $payIn->PaymentDetails->CardId = $parameters["CardId"];
        $payIn->ExecutionDetails = new PayInExecutionDetailsDirect();
        $payIn->ExecutionDetails->SecureMode = 'FORCE';
        $payIn->ExecutionDetails->SecureModeReturnURL = $parameters["SecureModeReturnURL"];
        $payIn->Tag = $parameters["Tag"];

        $payIn = $this->mangopay->api->PayIns->Create($payIn);

        $this->getLogger()->debug(
            'PayIn Card creation:' .
            '|-Id:' . $payIn->Id .
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

        $this->getLogger()->debug(
            'PayIn Card fetching:' .
            '|-Id:' . $payIn->Id .
            '|-Status:' . $payIn->Status .
            '|-ResultCode:' . $payIn->ResultCode .
            '|-ResultMessage:' . $payIn->ResultMessage
        );

        return $payIn;
    }
}

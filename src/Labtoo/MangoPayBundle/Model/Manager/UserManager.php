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

use Cocorico\MangoPayBundle\Request\UserRequest;
use Cocorico\MangoPayBundle\Model\Manager\UserManager as BaseUserManager;
use Cocorico\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use MangoPay\Transaction;

/**
 * Class UserManager.
 */
class UserManager extends BaseUserManager
{
    protected $userRequest;

    /**
     * @param UserRequest $userRequest
     */
    public function __construct(UserRequest $userRequest)
    {
        $this->userRequest = $userRequest;
    }

    /**
     * @param UserInterface|User $user
     *
     * @return \MangoPay\UserLegal|\MangoPay\UserNatural
     */
    public function createOrUpdate(UserInterface $user)
    {
        if ($user->getId() && $user->getMangopayId()) {
            return $this->update($user);
        } else {
            return $this->create($user);
        }
    }

    /**
     * @param UserInterface|User $user
     *
     * @return \MangoPay\UserLegal|\MangoPay\UserNatural
     */
    public function create(UserInterface $user)
    {
        $userAsArray = $this->toArray($user);
        $mangopayUser = $this->userRequest->create($userAsArray);

        return $mangopayUser;
    }

    /**
     * @param UserInterface $user
     *
     * @return \MangoPay\UserLegal
     */
    public function update(UserInterface $user)
    {
        $userAsArray = $this->toArray($user);
        $mangopayUser = $this->userRequest->update($userAsArray);

        return $mangopayUser;
    }

    /**
     * @param UserInterface|User $user
     *
     * @return array
     */
    private function toArray(UserInterface $user)
    {
        return array(
            'Id' => $user->getMangopayId(),
            'Email' => $user->getEmailLegal(),
            'FirstName' => $user->getFirstNameLegal(),
            'LastName' => $user->getLastNameLegal(),
            'Birthday' => $user->getBirthday()->getTimestamp(),
            'Nationality' => $user->getNationality(),
            'CountryOfResidence' => $user->getCountryOfResidence(),
            'CompanyName' => $user->getCompanyName(),
            'PersonType' => $user->getPersonType(),
        );
    }

    /**
     * Fetch all user transactions (payin, payout, ...) based on User ID.
     *
     * @param string $userId
     * @param array  $filters
     *                        ex :
     *                        'Status' => 'SUCCEEDED',
     *                        'Type' => 'PAYOUT',
     *                        'Nature' => 'REGULAR'
     *
     * @return Transaction[]
     */
    public function getTransactions($userId, array $filters)
    {
        return $this->userRequest->getTransactions($userId, $filters);
    }
}

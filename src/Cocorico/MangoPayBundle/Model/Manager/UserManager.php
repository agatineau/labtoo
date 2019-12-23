<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Cocorico\MangoPayBundle\Model\Manager;

use Cocorico\MangoPayBundle\Request\UserRequest;
use Cocorico\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use MangoPay\Transaction;

/**
 * Class UserManager.
 */
class UserManager
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
            'Email' => $user->getEmail(),
            'FirstName' => $user->getFirstName(),
            'LastName' => $user->getLastName(),
            'Birthday' => $user->getBirthday() ? $user->getBirthday()->getTimestamp() : 0,
            'Nationality' => $user->getNationality() ?: 'FR',
            'CountryOfResidence' => $user->getCountryOfResidence() ?: 'FR',
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

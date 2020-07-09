<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Entity;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

/**
 * @ORM\Entity(repositoryClass="Cocorico\BalanceBundle\Repository\BalanceMovementRepository")
 * @ORM\Table(name="balance_movement",indexes={
 *    @ORM\Index(name="status_bm_idx", columns={"status"}),
 *    @ORM\Index(name="created_at_bm_idx", columns={"created_at"}),
 *    @ORM\Index(name="validated_at_bm_idx", columns={"validated_at"}),
 *  })
 */
class BalanceMovement implements TranslationContainerInterface
{
    const TYPE_CREDIT = 1;
    const TYPE_CREDIT_CARD = 2;
    const TYPE_CREDIT_BANK_WIRE = 3;
    const TYPE_DEBIT = 4;
    const TYPE_RECOVER = 5;
    const TYPE_REFUND = 6;

    public static $typeTexts = array(
        self::TYPE_CREDIT => 'entity.balance_movement.type.credit',
        self::TYPE_CREDIT_CARD => 'entity.balance_movement.type.credit_card',
        self::TYPE_CREDIT_BANK_WIRE => 'entity.balance_movement.type.credit_bank_wire',
        self::TYPE_DEBIT => 'entity.balance_movement.type.debit',
        self::TYPE_RECOVER => 'entity.balance_movement.type.recover',
        self::TYPE_REFUND => 'entity.balance_movement.type.refund',
    );

    public static $typeCreditableTexts = array(
        self::TYPE_CREDIT_BANK_WIRE => 'entity.balance_movement.type.creditable.credit_bank_wire',
        self::TYPE_CREDIT_CARD => 'entity.balance_movement.type.creditable.credit_card',
    );

    public static $typePrefixes = array(
        self::TYPE_CREDIT => '+',
        self::TYPE_CREDIT_CARD => '+',
        self::TYPE_CREDIT_BANK_WIRE => '+',
        self::TYPE_DEBIT => '-',
        self::TYPE_RECOVER => '-',
        self::TYPE_REFUND => '+',
    );

    const STATUS_WAITING = 1;
    const STATUS_VALIDATE = 2;
    const STATUS_INVALIDATE = 3;

    public static $statusTexts = array(
        self::STATUS_WAITING => 'entity.balance_movement.status.waiting',
        self::STATUS_VALIDATE => 'entity.balance_movement.status.validate',
        self::STATUS_INVALIDATE => 'entity.balance_movement.status.invalidate',
    );

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(name="validated_at", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $validatedAt;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=0)
     *
     * @var int
     */
    private $amount;

    /**
     * @ORM\Column(type="smallint")
     *
     * @var int
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     *
     * @var int
     */
    private $status;

    /**
     * @ORM\Column(name="mangopay_id", type="integer", nullable=true)
     *
     * @var int
     */
    private $mangopayId;

    /**
     * @ORM\Column(name="mangopay_card_id", type="integer", nullable=true)
     *
     * @var int
     */
    private $mangopayCardId;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\UserBundle\Entity\User", inversedBy="balanceMovements")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Cocorico\CoreBundle\Entity\Booking", inversedBy="balanceMovements")
     *
     * @var Booking
     */
    private $booking;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getValidatedAt()
    {
        return $this->validatedAt;
    }

    /**
     * @param \DateTime $validatedAt
     */
    public function setValidatedAt($validatedAt)
    {
        $this->validatedAt = $validatedAt;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return int
     */
    public function getAmountDecimal()
    {
        return $this->getAmount() / 100;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for balance_movement.amount: %s', $amount)
            );
        }
        $this->amount = $amount;
    }

    /**
     * @param  integer $type
     */
    public function setType($type)
    {
        if (!in_array($type, array_keys(self::$typeTexts))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for balance_movement.type : %s', $type)
            );
        }
        $this->type = $type;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTypeText()
    {
        return self::$typeTexts[$this->type];
    }

    /**
     * @return string
     */
    public function getTypePrefix()
    {
        return self::$typePrefixes[$this->type];
    }

    /**
     * @param integer $status
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusTexts))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for balance_movement.status : %s', $status)
            );
        }
        $this->status = $status;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return integer
     */
    public function getStatusText()
    {
        return self::$statusTexts[$this->status];
    }

    /**
     * @return int
     */
    public function getMangopayId()
    {
        return $this->mangopayId;
    }

    /**
     * @param int $mangopayId
     */
    public function setMangopayId($mangopayId)
    {
        $this->mangopayId = $mangopayId;
    }

    /**
     * @return int
     */
    public function getMangopayCardId()
    {
        return $this->mangopayCardId;
    }

    /**
     * @param int $mangopayCardId
     */
    public function setMangopayCardId($mangopayCardId)
    {
        $this->mangopayCardId = $mangopayCardId;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Booking
     */
    public function getBooking()
    {
        return $this->booking;
    }

    /**
     * @param Booking $booking
     */
    public function setBooking($booking)
    {
        $this->booking = $booking;
    }

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        foreach (self::$typeTexts as $text) {
            $messages [] = new Message($text, 'cocorico_balance');
        }
        foreach (self::$typeCreditableTexts as $text) {
            $messages [] = new Message($text, 'cocorico_balance');
        }
        foreach (self::$statusTexts as $text) {
            $messages [] = new Message($text, 'cocorico_balance');
        }

        return $messages;
    }
}

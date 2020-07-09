<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CreditConstraint extends Constraint implements TranslationContainerInterface
{
    /**
     * @var string
     */
    public static $messageType = 'credit.type.error';

    /**
     * @var string
     */
    public static $messageAmount = 'credit.amount.error';

    /**
     * @var string
     */
    public static $messageTac = 'credit.tac.error';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'credit';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$messageType, 'cocorico_balance');
        $messages[] = new Message(self::$messageAmount, 'cocorico_balance');
        $messages[] = new Message(self::$messageTac, 'validators');

        return $messages;
    }
}

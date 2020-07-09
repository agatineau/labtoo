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
class RecoverCheckConstraint extends Constraint implements TranslationContainerInterface
{
    /**
     * @var string
     */
    public static $messagePassword = 'recover_check.password.error';

    /**
     * @var string
     */
    public static $messageTac = 'recover_check.tac.error';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'recover_check';
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
        $messages[] = new Message(self::$messagePassword, 'cocorico_balance');
        $messages[] = new Message(self::$messageTac, 'validators');

        return $messages;
    }
}

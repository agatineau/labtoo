<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Exception;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;

class RecoverInsufficientAmountException extends \Exception implements TranslationContainerInterface
{
    /**
     * @var string
     */
    public static $tag = 'recover.insufficient_amount.exception';

    /**
     * RecoverInsufficientAmountException constructor.
     */
    public function __construct()
    {
        parent::__construct(self::$tag);
    }

    /**
     * @return array
     */
    public static function getTranslationMessages()
    {
        return array(
            new Message(self::$tag, 'cocorico_balance')
        );
    }
}

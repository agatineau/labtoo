<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Validator\Constraints;

use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Experiment extends Constraint implements TranslationContainerInterface
{
    public static $messageDependency = 'experiment.new.error.dependency';
    public static $messageRecursion = 'experiment.new.error.recursion';

    public function validatedBy()
    {
        return 'experiment';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();
        $messages[] = new Message(self::$messageDependency, 'cocorico_experiment');
        $messages[] = new Message(self::$messageRecursion, 'cocorico_experiment');
        return $messages;
    }
}

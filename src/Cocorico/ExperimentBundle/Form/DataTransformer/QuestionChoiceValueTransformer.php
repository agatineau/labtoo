<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class QuestionChoiceValueTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform($value)
    {
        return explode('|', $value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function reverseTransform($value)
    {
        return implode('|', $value);
    }

}

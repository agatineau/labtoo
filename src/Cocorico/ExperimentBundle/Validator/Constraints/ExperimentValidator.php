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

use Cocorico\ExperimentBundle\Entity\Experiment as ExperimentEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExperimentValidator extends ConstraintValidator
{
    /**
     * @param ExperimentEntity|mixed $experiment
     * @param Experiment|Constraint $constraint
     */
    public function validate($experiment, Constraint $constraint)
    {
        $violations = $this->getViolations($experiment, $constraint);

        if (count($violations)) {
            foreach ($violations as $violation) {
                $message = $violation['message'];
                $atPath = isset($violation['atPath']) ? $violation['atPath'] : null;
                $domain = isset($violation['domain']) ? $violation['domain'] : 'cocorico_experiment';
                $parameters = isset($violation['parameter']) ? $violation['parameter'] : array();
                reset($parameters);
                foreach ($parameters as $key => $value) {
                    $parameters['{{ ' . $key . ' }}'] = $value;
                }

                if ($parameters) {
                    $this->context->buildViolation($message)
                        ->atPath($atPath)
                        ->setParameters($parameters)
                        ->setTranslationDomain($domain)
                        ->addViolation();
                } else {
                    $this->context->buildViolation($message)
                        ->atPath($atPath)
                        ->setTranslationDomain($domain)
                        ->addViolation();
                }
            }
        }
    }

    /**
     * @param ExperimentEntity $experiment
     * @param Experiment|Constraint $constraint
     * @return array
     */
    private function getViolations($experiment, $constraint)
    {
        $violations = array();

        // Dependency
        if ($experiment->getQuestionnaire()->getEntries()->count()) {
            $values = array();
            foreach ($experiment->getQuestionnaire()->getEntries() as $entry) {
                if (!$entry->getDependencyUid()) continue;
                if (in_array($entry->getDependencyUid(), $values)) {
                    $this->context->buildViolation($constraint::$messageDependency)
                        ->setTranslationDomain('cocorico_experiment')
                        ->addViolation();
                }
                $values [] = $entry->getDependencyUid();
            }
        }

        // Recursion
        if (
            $experiment->getQuestionnaire()->getQuestionRootUid() ||
            $experiment->getQuestionnaire()->getEntries()->count()
        ) {
            $values = array($experiment->getQuestionnaire()->getQuestionRootUid());
            foreach ($experiment->getQuestionnaire()->getEntries() as $entry) {
                if (!$entry->getQuestionUid()) continue;
                if (in_array($entry->getQuestionUid(), $values)) {
                    $this->context->buildViolation($constraint::$messageRecursion)
                        ->setTranslationDomain('cocorico_experiment')
                        ->addViolation();
                }
                $values [] = $entry->getQuestionUid();
            }
        }

        return $violations;
    }
}

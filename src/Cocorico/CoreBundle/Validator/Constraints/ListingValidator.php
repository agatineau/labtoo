<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ListingValidator extends ConstraintValidator
{
    private $emr;
    private $maxImages;
    private $minImages;
    private $minCategories;
    private $minPrice;
    private $countries;
    private $securityTokenStorage;

    /**
     * @param EntityManager $emr
     * @param               $maxImages
     * @param               $minImages
     * @param               $minCategories
     * @param               $minPrice
     * @param               $countries
     */
    public function __construct(EntityManager $emr, $maxImages, $minImages, $minCategories, $minPrice, $countries, TokenStorage $securityTokenStorage)
    {
        $this->emr = $emr;
        $this->maxImages = $maxImages;
        $this->minImages = $minImages;
        $this->minCategories = $minCategories;
        $this->minPrice = $minPrice;
        $this->countries = $countries;
        $this->securityTokenStorage = $securityTokenStorage;
    }

    /**
     * @param mixed      $listing
     * @param Constraint $constraint
     */
    public function validate($listing, Constraint $constraint)
    {
        /** @var \Cocorico\CoreBundle\Entity\Listing $listing */
        /** @var \Cocorico\CoreBundle\Validator\Constraints\Listing $constraint */

        //Images
        /*if ($listing->getImages()->count() > $this->maxImages) {
            $this->context->buildViolation($constraint::$messageMaxImages)
                ->atPath('image[new]')
                ->setParameter('{{ max_images }}', $this->maxImages)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }

        if ($listing->getImages()->count() < $this->minImages) {
            $this->context->buildViolation($constraint::$messageMinImages)
                ->atPath('image[new]')
                ->setParameter('{{ min_images }}', $this->minImages)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }*/

        $user = $this->securityTokenStorage->getToken()->getUser()->getId();
        $listingCount = $this->emr->getRepository("CocoricoCoreBundle:Listing")->findOneByExperiment($listing->getExperiment()->getId(), $user);

        if (count($listingCount)>0 && !$listing->getId()) {
            $this->context->buildViolation($constraint::$messageExperiment)
                ->atPath('experiment')
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }

        //Categories
        if ($listing->getListingListingCategories()->count() < $this->minCategories) {
            $this->context->buildViolation($constraint::$messageMinCategories)
                ->atPath('listingListingCategories')
                ->setParameter('{{ min_categories }}', $this->minCategories)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }

        //Price
        /*if ($listing->getPrice() < $this->minPrice) {
            $this->context->buildViolation($constraint::$messageMinPrice)
                ->atPath('price')
                ->setParameter('{{ min_price }}', $this->minPrice / 100)
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }*/

        //Duration
        if ($listing->getMinDuration() && $listing->getMaxDuration() &&
            $listing->getMinDuration() > $listing->getMaxDuration()
        ) {
            $this->context->buildViolation($constraint::$messageDuration)
                ->atPath('min_duration')
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }


        //Location
        if ($this->countries && !in_array($listing->getLocation()->getCountry(), $this->countries)) {
            $this->context->buildViolation($constraint::$messageCountryInvalid)
                ->atPath('location.country')
                ->setTranslationDomain('cocorico_listing')
                ->addViolation();
        }

        //Status
//        $oldListing = $this->emr->getUnitOfWork()->getOriginalEntityData($listing);
//
//        if (is_array($oldListing) and !empty($oldListing)) {
//            $oldStatus = $oldListing['status'];
//            if ($oldStatus == $listing::STATUS_INVALIDATED && $listing->getStatus() != $listing::STATUS_INVALIDATED) {
//                $listing->setStatus($oldStatus);
//                $this->context->buildViolation($constraint::$messageStatusInvalidated)
//                    ->atPath('status')
//                    ->setTranslationDomain('cocorico_listing')
//                    ->addViolation();
//            }
//        }

        //Answers
        if ($listing->getAnswers()->count()) {
            foreach ($listing->getAnswers() as $index => $answer) {
                if (!$answer->getValue()) {
                    $this->context->buildViolation($constraint::$messageAnswerRequired)
                        ->atPath(sprintf('answers[%d].value', $index))
                        ->setTranslationDomain('cocorico_listing')
                        ->addViolation();
                }
            }
        }
    }

}

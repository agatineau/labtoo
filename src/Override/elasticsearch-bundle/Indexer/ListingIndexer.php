<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Override\ElasticsearchBundle\Indexer;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingCategoryTranslation;
use Cocorico\CoreBundle\Entity\ListingTranslation;
use Cocorico\ElasticsearchBundle\Indexer\BaseIndexer;
use Cocorico\ElasticsearchBundle\Indexer\IndexerInterface;
use Cocorico\UserBundle\Entity\UserTranslation;
use Doctrine\ORM\EntityManager;
use Elastica\Document;


class ListingIndexer extends BaseIndexer implements IndexerInterface
{
    const FIELD_TITLE = 'title';
    /*const FIELD_DESCRIPTION = 'description';*/
    const FIELD_KEYWORDS = 'keywords';
    const FIELD_CATEGORY_NAMES = 'category_names';
    //const FIELD_USER_DESCRIPTION = 'user_description';

    private $fields = array(
        self::FIELD_TITLE,
        /*self::FIELD_DESCRIPTION,*/
        self::FIELD_KEYWORDS,
        self::FIELD_CATEGORY_NAMES,
        //self::FIELD_USER_DESCRIPTION
    );

    private $fieldBoosts = array(
        self::FIELD_TITLE => self::DEFAULT_BOOST,
        /*self::FIELD_DESCRIPTION => self::DEFAULT_BOOST,*/
        self::FIELD_KEYWORDS => self::DEFAULT_BOOST,
        self::FIELD_CATEGORY_NAMES => self::DEFAULT_BOOST,
        //self::FIELD_USER_DESCRIPTION => self::DEFAULT_BOOST
    );

    private $fieldTypes = array(
        self::FIELD_TITLE => self::TYPE_TRANSLATABLE_TEXT,
        /*self::FIELD_DESCRIPTION => self::TYPE_TRANSLATABLE_TEXT,*/
        self::FIELD_KEYWORDS => self::TYPE_TRANSLATABLE_TEXT,
        self::FIELD_CATEGORY_NAMES => self::TYPE_TRANSLATABLE_TEXT,
        //self::FIELD_USER_DESCRIPTION => self::TYPE_TRANSLATABLE_TEXT
    );

    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param int $field
     * @return int
     */
    public function getFieldBoost($field)
    {
        return $this->fieldBoosts[$field];
    }

    /**
     * @param int $field
     * @return string
     */
    public function getFieldType($field)
    {
        return $this->fieldTypes[$field];
    }

    /**
     * @param Document $document
     * @param mixed $object
     */
    public function fillDocument(Document $document, $object)
    {
        $categoryRepository = $this->em->getRepository('CocoricoCoreBundle:ListingCategory');
        /** @var Listing $listing */
        $listing = $object;
        foreach ($this->getFields() as $field) {
            switch ($field) {
                case self::FIELD_TITLE:
                    /** @var ListingTranslation $listingTranslation */
                    foreach ($listing->getTranslations() as $listingTranslation) {
                        $document->set(sprintf(
                            '%s_%s',
                            $field,
                            $listingTranslation->getLocale()
                        ), $listingTranslation->getTitle());
                    }
                    break;
                /*case self::FIELD_DESCRIPTION:
                    foreach ($listing->getTranslations() as $listingTranslation) {
                        $document->set(sprintf(
                            '%s_%s',
                            $field,
                            $listingTranslation->getLocale()
                        ), $listingTranslation->getDescription());
                    }
                    break;*/
                case self::FIELD_KEYWORDS:
                    /** @var ListingTranslation $listingTranslation */
                    foreach ($listing->getTranslations() as $listingTranslation) {
                        $document->set(sprintf(
                            '%s_%s',
                            $field,
                            $listingTranslation->getLocale()
                        ), $listingTranslation->getKeywords());
                    }
                    break;
                case self::FIELD_CATEGORY_NAMES:
                    $categories = array();

                        foreach ($categoryRepository->getPath($listing->getExperiment()->getCategory()) as $pathCategory) {

                            foreach ($pathCategory->getTranslations() as $listingCategoryTranslation) {
                                if (!isset($categories[$listingCategoryTranslation->getLocale()])) {
                                    $categories[$listingCategoryTranslation->getLocale()] = array();
                                }
                                $categories[$listingCategoryTranslation->getLocale()][] = $listingCategoryTranslation->getName();

                            }
                        }

                    foreach ($categories as $locale => $category_names) {
                        $document->set(sprintf(
                            '%s_%s',
                            $field,
                            $locale
                        ), implode(' ', $category_names));
                    }
                    break;
                /*case self::FIELD_USER_DESCRIPTION:
                    foreach ($listing->getUser()->getTranslations() as $userTranslation) {
                        $document->set(sprintf(
                            '%s_%s',
                            $field,
                            $userTranslation->getLocale()
                        ), $userTranslation->getDescription());
                    }
                    break;*/
            }
        }
    }

    /**
     * @param mixed $object
     * @return bool
     */
    public function supports($object)
    {
        return $object instanceof Listing;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            preg_match('/(\w+).(\w+)/', $key, $match);

            if ($match[2] == 'boost') {
                $this->fieldBoosts[$match[1]] = $value;
            } elseif ($match[2] == 'type') {
                $this->fieldTypes[$match[1]] = $value;
            }
        }
    }
}

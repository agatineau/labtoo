<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;


/**
 * HeaderText
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="header_text")
 *
 */
class HeaderText
{
    use ORMBehaviors\Translatable\Translatable;

    const SECTION_TYPE_BANNER = 1;
    const SECTION_TYPE_BLOG = 2;

    public static $sectionTypeValues = array(
        self::SECTION_TYPE_BANNER => 'entity.header_text.section_type.banner',
        self::SECTION_TYPE_BLOG => 'entity.header_text.section_type.blog',
    );

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     *
     * @ORM\Column(name="published", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $published;

    /**
     * @var string
     *
     * @ORM\Column(name="section_type", type="smallint", nullable=true)
     *
     * @var boolean
     */
    protected $sectionType = self::SECTION_TYPE_BANNER;


    public function __construct()
    {
        $this->published = true;
    }

    /**
     * Translation proxy.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * @param boolean $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * Get published
     *
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    public function getDescription()
    {
        return (string)$this->translate()->getDescription();
    }

    public function getUrl()
    {
        return (string)$this->translate()->getUrl();
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function setSectionType($sectionType)
    {
        if (!in_array($sectionType, array_keys(self::$sectionTypeValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid section type value', $sectionType)
            );
        }

        $this->sectionType = $sectionType;

        return $this;
    }

    public function getSectionType()
    {
        if (!$this->sectionType) {
            $this->sectionType = self::SECTION_TYPE_BANNER;
        }

        return $this->sectionType;
    }

    public function getSectionTypeText()
    {
        return self::$sectionTypeValues[$this->getSectionType()];
    }
}

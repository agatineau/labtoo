<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Entity\Listing;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class BaseListingTranslation
{

    /**
     *
     * @ORM\Column(name="title", type="string", nullable=true)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(name="keywords", type="text", length=65535, nullable=true)
     *
     * @var string
     */
    protected $keywords;

    /**
     * @ORM\Column(name="rules", type="text", length=65535, nullable=true)
     *
     * @var string
     */
    protected $rules;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @Assert\NotNull(message="assert.not_blank")
     * @Assert\Length(max = 5000)
     *
     * @ORM\Column(name="informative_description", type="text", length=65535, nullable=true)
     *
     * @var string
     */
    protected $informativeDescription;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTranslatableId()
    {
        return $this->translatable->getId();
    }

    public function getSluggableFields()
    {
        return ['title', 'translatableId'];
    }

    /**
     * Set title
     *
     * @param  string $title
     * @return Listing
     */
    public function setTitle($title)
    {
        $this->title = ucfirst($title);

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set keywords
     *
     * @param  string $keywords
     * @return Listing
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return Listing
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param  string $rules
     * @return Listing
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get rules
     *
     * @return string
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Set informativeDescription
     *
     * @param  string $informativeDescription
     * @return Listing
     */
    public function setInformativeDescription($informativeDescription)
    {
        $this->informativeDescription = $informativeDescription;

        return $this;
    }

    /**
     * Get informativeDescription
     *
     * @return string
     */
    public function getInformativeDescription()
    {
        return $this->informativeDescription;
    }

}

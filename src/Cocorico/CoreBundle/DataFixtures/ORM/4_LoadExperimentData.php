<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\DataFixtures\ORM;

use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\ExperimentBundle\Entity\ExperimentImage;
use Cocorico\ExperimentBundle\Entity\ExperimentTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExperimentData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $experiment = new Experiment();
        $experiment->setPublished(true);
        $experiment->translate('en')->setTitle('Experiment title');
        $experiment->translate('fr')->setTitle('Titre de l\'expérience');
        $experiment->translate('en')->setDescription('Experiment description');
        $experiment->translate('fr')->setDescription('Description de l\'expérience');
        $experiment->translate('en')->setKeywords('hello');
        $experiment->translate('fr')->setKeywords('coucou');
        $experiment->setFormula(500);

        $image = new ExperimentImage();
        $image->setName(ExperimentImage::IMAGE_DEFAULT);
        $experiment->setImage($image);

        /** @var ListingCategory $category */
        $category = $manager->merge($this->getReference('category1_1'));
        $experiment->setCategory($category);

        $manager->persist($experiment);
        $experiment->mergeNewTranslations();
        $manager->flush();

        /** @var ExperimentTranslation $translation */
        foreach ($experiment->getTranslations() as $i => $translation) {
            $translation->generateSlug();
        }
        $manager->flush();

        $this->addReference('experiment-1', $experiment);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }

}

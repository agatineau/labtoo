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

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Entity\ListingCategory;
use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingCharacteristicValue;
use Cocorico\CoreBundle\Entity\ListingImage;
use Cocorico\CoreBundle\Entity\ListingListingCategory;
use Cocorico\CoreBundle\Entity\ListingListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingLocation;
use Cocorico\CoreBundle\Entity\ListingTranslation;
use Cocorico\ExperimentBundle\Entity\Experiment;
use Cocorico\GeoBundle\Entity\Area;
use Cocorico\GeoBundle\Entity\City;
use Cocorico\GeoBundle\Entity\Coordinate;
use Cocorico\GeoBundle\Entity\Country;
use Cocorico\GeoBundle\Entity\Department;
use Cocorico\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadListingData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        //GeoGraphical entities
        $country = new Country();
        $country->setCode("FR");
        $country->translate('en')->setName('France');
        $country->translate('fr')->setName('France');

        $area = new Area();
        $area->setCountry($country);
        $area->translate('en')->setName('Île-de-France');
        $area->translate('fr')->setName('Île-de-France');

        $department = new Department();
        $department->setCountry($country);
        $department->setArea($area);
        $department->translate('en')->setName('Paris');
        $department->translate('fr')->setName('Paris');

        $city = new City();
        $city->setCountry($country);
        $city->setArea($area);
        $city->setDepartment($department);
        $city->translate('en')->setName('Paris');
        $city->translate('fr')->setName('Paris');

        $manager->persist($country);
        $manager->persist($area);
        $manager->persist($department);
        $manager->persist($city);
        $country->mergeNewTranslations();
        $area->mergeNewTranslations();
        $department->mergeNewTranslations();
        $city->mergeNewTranslations();

        //Coordinate entity
        $coordinate = new Coordinate();
        $coordinate->setCountry($country);
        $coordinate->setArea($area);
        $coordinate->setDepartment($department);
        $coordinate->setCity($city);
        $coordinate->setZip("75002");
        $coordinate->setRoute("Rue de la Lune");
        $coordinate->setStreetNumber("9");
        $coordinate->setAddress("9 Rue de la Lune, 75002 Paris, France");
        $coordinate->setLat(48.8697174);
        $coordinate->setLng(2.3509855);
        $manager->persist($coordinate);

        //Listing Location
        $location = new ListingLocation();
        $location->setCountry("FR");
        $location->setCity("Paris");
        $location->setZip("75002");
        $location->setRoute("rue de la Lune");
        $location->setStreetNumber("9");
        $location->setCoordinate($coordinate);
        $manager->persist($location);

        //Listing Image
        $image1 = new ListingImage();
        $image1->setName(ListingImage::IMAGE_DEFAULT);
        $image1->setPosition(1);

        $image2 = new ListingImage();
        $image2->setName(ListingImage::IMAGE_DEFAULT);
        $image2->setPosition(2);

        //Listing
        $listing = new Listing();
        $listing->setLocation($location);
        $listing->addImage($image1);
        $listing->addImage($image2);
        $listing->translate('en')->setTitle('Experiment title');
        $listing->translate('fr')->setTitle('Titre de l\'expérience');
        $listing->translate('en')->setDescription('Experiment description');
        $listing->translate('fr')->setDescription('Description de l\'expérience');
        $listing->translate('en')->setKeywords('hello');
        $listing->translate('fr')->setKeywords('coucou');
        $listing->setStatus(Listing::STATUS_PUBLISHED);
        $listing->setPrice(null);
        $listing->setCertified(1);
        $listing->setDuration(21);

        /** @var User $user */
        $user = $manager->merge($this->getReference('offerer'));
        $listing->setUser($user);

        /** @var ListingCategory $category */
        $category = $manager->merge($this->getReference('category1_1'));
        $listingCategory = new ListingListingCategory();
        $listingCategory->setListing($listing);
        $listingCategory->setCategory($category);
        $listing->addListingListingCategory($listingCategory);

        /** @var Experiment $experiment */
        $experiment = $manager->merge($this->getReference('experiment-1'));
        $listing->setExperiment($experiment);

        $manager->persist($listing);
        $listing->mergeNewTranslations();
        $manager->flush();


        /** @var ListingTranslation $translation */
        foreach ($listing->getTranslations() as $i => $translation) {
            $translation->generateSlug();
        }
        $manager->persist($listing);
        $manager->flush();

        $this->addReference('listing-one', $listing);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 8;
    }

}

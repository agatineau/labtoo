<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ExperimentBundle\Command;

use Cocorico\CoreBundle\Entity\Listing;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexListingsCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:listings:index')
            ->setHelp("Usage php app/console cocorico:listings:index");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $listings = $em->getRepository('CocoricoCoreBundle:Listing')->findAll();

        /** @var Listing $listing */
        foreach ($listings as $listing) {
            $searchableAnswerValues = array();
            foreach ($listing->getAnswers() as $answer) {
                $searchableAnswerValues = array_merge($searchableAnswerValues, $answer->getSearchableValues());
            }
            $listing->setSearchableAnswerValues($searchableAnswerValues);
        }

        $em->flush();

    }
}

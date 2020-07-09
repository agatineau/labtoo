<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Command;

use Cocorico\CoreBundle\Repository\ListingCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RepairListingCategoriesCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('cocorico:listing_categories:repair');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var ListingCategoryRepository $repo */
        $repo = $em->getRepository('CocoricoCoreBundle:ListingCategory');
        $repo->verify();
        $repo->recover();
        $em->flush();
    }
}

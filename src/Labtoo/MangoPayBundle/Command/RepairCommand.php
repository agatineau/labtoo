<?php

/*
* This file is part of the Cocorico package.
*
* (c) Cocolabs SAS <contact@cocolabs.io>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Labtoo\MangoPayBundle\Command;

use Cocorico\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RepairCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('cocorico_mangopay:repair');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mangopayApi = $this->getContainer()->get('cocorico_mangopay.api');
        $userManager = $this->getContainer()->get('cocorico_user.user_manager');

        /** @var User[] $users */
        $users = $userManager->findUsers();

        foreach ($users as $i => $user) {
            $wallet = $mangopayApi->api->Wallets->Get($user->getMangopayWalletId());
            $user->setMangopayId($wallet->Owners[0]);
            $userManager->persistAndFlush($user);
        }

        $output->writeln(count($users) . " users(s) repaired");
    }
}

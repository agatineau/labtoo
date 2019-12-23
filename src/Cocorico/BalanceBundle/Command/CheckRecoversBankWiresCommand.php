<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\BalanceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

//Cron: 0 */2  * * *  user   php app/console cocorico_balance:recovers:checkBankWires

class CheckRecoversBankWiresCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('cocorico_balance:recovers:checkBankWires')
            ->setDescription('Check Recovers Bank Wires.')
            ->setHelp('Usage php app/console cocorico_balance:recovers:checkBankWires');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->getContainer()
            ->get('cocorico_balance.manager.recover')->checkBankWires();
        $output->writeln(sprintf('%d recover(s) bank wires checked', $result));
    }
}

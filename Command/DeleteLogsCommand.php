<?php

namespace NTI\LogBundle\Command;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NTI\LogBundle\Entity\Log;

class DeleteLogsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('nti:log:delete')
            ->setDescription('Delete All Logs past 30 Days')
            ->addArgument('limit', InputArgument::OPTIONAL, 'Days');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Started Delete Logs.");
        $limit = $input->getArgument('limit') ?? 60;

        $em = $this->getContainer()->get('doctrine')->getManager();
        $month = new \DateTime('-'.$limit . ' days');

        /** @var QueryBuilder $qb */
        $qb = $em->getRepository(Log::class)->createQueryBuilder('l');
        $qb->andWhere('l.date <= :month')->setParameter('month', $month);
        $qb->delete();
        $result = $qb->getQuery()->execute();

        $output->writeln("Finished delete logs ({$result}).");
    }
}

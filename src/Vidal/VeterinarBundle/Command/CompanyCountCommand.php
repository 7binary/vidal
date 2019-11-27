<?php
namespace Vidal\VeterinarBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyCountCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:company_count');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);

        /** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');
        $pdo = $em->getConnection();

		$output->writeln('--- veterinar:company_count started');

		$repo      = $em->getRepository('VidalVeterinarBundle:Product');
		$companies = $em->getRepository('VidalVeterinarBundle:Company')->findAll();

		# ставим сколько всего у них препаратов
		for ($i = 0; $i < count($companies); $i++) {
			$count = $repo->countByCompanyID($companies[$i]->getCompanyID());
			$companies[$i]->setCountProducts($count);
            $pdo->prepare("UPDATE company SET countProducts = $count WHERE CompanyID = {$companies[$i]->getCompanyID()}")->execute();

        }

		$em->flush();

		$output->writeln('+++ veterinar:company_count completed');
	}
}
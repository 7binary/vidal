<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanyCountCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:company_count');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$output->writeln('--- vidal:company_count started');

		$repo      = $em->getRepository('VidalDrugBundle:Product');
		$companies = $em->getRepository('VidalDrugBundle:Company')->findAll();

		# ставим сколько всего у них препаратов
		for ($i = 0; $i < count($companies); $i++) {
			$count = $repo->countByCompanyID($companies[$i]->getCompanyID());
			$companies[$i]->setCountProducts($count);
		}

		$em->flush();

		$output->writeln('+++ vidal:company_count completed');
	}
}
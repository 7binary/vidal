<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArtUrlCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:art_url');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $output->writeln('--- vidal:art_url started');

        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $pdo->prepare("UPDATE art_rubrique SET url = LOWER(url)")->execute();
        $pdo->prepare("UPDATE art_type SET url = LOWER(url)")->execute();
        $pdo->prepare("UPDATE art_category SET url = LOWER(url)")->execute();
        $pdo->prepare("UPDATE art SET link = LOWER(link)")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,' ','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,':','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,'_','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,'(','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,')','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,'«','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,'»','-')")->execute();

        $pdo->prepare("UPDATE art SET link = REPLACE(link,'----','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,'---','-')")->execute();
        $pdo->prepare("UPDATE art SET link = REPLACE(link,'--','-')")->execute();

        $pdo->prepare("UPDATE art SET link = REPLACE(link,'&#8232;','')")->execute();
        $pdo->prepare("UPDATE art SET title = REPLACE(title,'&#8232;','')")->execute();

        $output->writeln('--- vidal:art_url finished');
	}
}
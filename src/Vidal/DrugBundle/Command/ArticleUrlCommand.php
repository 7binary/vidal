<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArticleUrlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:article_url');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:article_url started');

        $em = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();

		$pdo->prepare("UPDATE article_rubrique SET rubrique = LOWER(rubrique)")->execute();
		$pdo->prepare("UPDATE article SET link = LOWER(link)")->execute();
		$pdo->prepare("UPDATE article SET link = REPLACE(link,'_','-')")->execute();

        $output->writeln('--- vidal:article_url finished');
    }
}
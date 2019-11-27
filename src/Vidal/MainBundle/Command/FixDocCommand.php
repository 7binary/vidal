<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixDocCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:fix_doc');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:fix_doc started');

        $container   = $this->getContainer();
        /** @var EntityManager $em */
        $em          = $container->get('doctrine')->getManager('drug');
        $pdo         = $em->getConnection();

        $lines = file(__DIR__ .'/Data/doc.txt', FILE_IGNORE_NEW_LINES);

        foreach ($lines as $line) {
            $line = preg_replace('/[^0-9]/i', ' ', $line);
            list($documentId, $year) = explode(' ', $line);

            if (!empty($documentId) && !empty($year)) {
                $pdo->prepare("UPDATE document SET YearEdition=$year WHERE DocumentID=$documentId")->execute();
            }
        }

		$output->writeln("+++ vidal:fix_doc completed!");
	}
}
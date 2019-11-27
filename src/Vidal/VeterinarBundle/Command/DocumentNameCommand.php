<?php
namespace Vidal\VeterinarBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации нормальных имен для препаратов
 *
 * @package Vidal\VeterinarBundle\Command
 */
class DocumentNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:document_name')
			->setDescription('Adds Document.Name');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:document_name started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		# надо установить имена для препаратов без тегов/пробелов в нижний регистр
		$em->createQuery('
			UPDATE VidalVeterinarBundle:Document d
			SET d.Name = LOWER(d.EngName)
			WHERE d.EngName NOT LIKE \'%<%\' AND
				d.EngName NOT LIKE \'% %\' AND
				d.EngName NOT LIKE \'%/%\' AND
				d.EngName NOT LIKE \'%&%\'
		')->execute();

		# далее надо преобразовать остальные по регуляркам
		$count = $em->createQuery('
			SELECT COUNT(d.DocumentID)
			FROM VidalVeterinarBundle:Document d
			WHERE d.CountryEditionCode = \'RUS\' AND
			 	(d.EngName LIKE \'%<%\' OR d.EngName LIKE \'% %\' OR d.EngName LIKE \'%/%\' OR d.EngName LIKE \'%&%\')
		')->getSingleScalarResult();

		$query = $em->createQuery('
			SELECT d.DocumentID, d.EngName
			FROM VidalVeterinarBundle:Document d
			WHERE d.CountryEditionCode = \'RUS\' AND
				(d.EngName LIKE \'%<%\' OR d.EngName LIKE \'% %\' OR d.EngName LIKE \'%/%\' OR d.EngName LIKE \'%&%\')
		');

		$updateQuery = $em->createQuery('
			UPDATE VidalVeterinarBundle:Document d
			SET d.Name = :document_name
			WHERE d = :document_id
		');

		$step = 100;

		for ($i = 0, $c = $count; $i < $c; $i = $i+$step) {
			$documents = $query
				->setFirstResult($i)
				->setMaxResults($i+$step)
				->getResult();

			foreach ($documents as $document) {
				$p    = array('/&(.+);/', '/ /', '/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i');
				$r    = array('_$1_', '-', '', '');
				$name = preg_replace($p, $r, $document['EngName']);
				$name = preg_replace('/\\//i', '-', $name);
				$name = mb_strtolower($name, 'UTF-8');

				$updateQuery->setParameters(array(
					'document_name' => $name,
					'document_id'   => $document['DocumentID'],
				))->execute();
			}
        }

		$output->writeln('+++ veterinar:document_name completed!');
	}
}
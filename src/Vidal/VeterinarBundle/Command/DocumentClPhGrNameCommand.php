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
class DocumentClPhGrNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('veterinar:document_clphgrname')
			->setDescription('Adds Document.Name');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:document_clphgrname started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');

		# надо установить имена для препаратов без тегов/пробелов в нижний регистр
		$em->createQuery('
			UPDATE VidalVeterinarBundle:Document d
			SET d.ClPhGrName = d.ClPhGrDescription
			WHERE d.ClPhGrDescription NOT LIKE \'%<%\' AND
				d.ClPhGrDescription IS NOT NULL
		')->execute();

		# далее надо преобразовать остальные по регуляркам
		$count = $em->createQuery('
			SELECT COUNT(d.DocumentID)
			FROM VidalVeterinarBundle:Document d
			WHERE d.CountryEditionCode = \'RUS\' AND
			 	(d.ClPhGrDescription LIKE \'%<%\' OR d.ClPhGrDescription IS NULL)
		')->getSingleScalarResult();

		$query = $em->createQuery('
			SELECT d.DocumentID, d.ClPhGrDescription
			FROM VidalVeterinarBundle:Document d
			WHERE d.CountryEditionCode = \'RUS\' AND
				(d.ClPhGrDescription LIKE \'%<%\' OR d.ClPhGrDescription IS NULL)
		');

		$updateQuery = $em->createQuery('
			UPDATE VidalVeterinarBundle:Document d
			SET d.ClPhGrName = :document_clphgrname
			WHERE d = :document_id
		');

		$step = 100;

		for ($i = 0, $c = $count; $i < $c; $i = $i + $step) {
			$documents = $query
				->setFirstResult($i)
				->setMaxResults($i + $step)
				->getResult();

			foreach ($documents as $document) {
				$p    = array('/<BR( ?)\/>/i', '/<su.>(.*?)<\/su.>/i');
				$r    = array('. ', '$1');
				$name = preg_replace($p, $r, $document['ClPhGrDescription']);

				$updateQuery->setParameters(array(
					'document_clphgrname' => $name,
					'document_id'         => $document['DocumentID'],
				))->execute();
			}
		}

		$output->writeln('+++ veterinar:document_clphgrname completed!');
	}
}
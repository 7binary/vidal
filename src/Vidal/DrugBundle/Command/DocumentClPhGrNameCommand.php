<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации нормальных имен для препаратов
 *
 * @package Vidal\DrugBundle\Command
 */
class DocumentClPhGrNameCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:document_clphgrname')
			->setDescription('Adds Document.Name');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:document_clphgrname started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		# надо установить имена для препаратов без тегов/пробелов в нижний регистр
		$em->createQuery('
			UPDATE VidalDrugBundle:Document d
			SET d.ClPhGrName = d.ClPhGrDescription
			WHERE d.ClPhGrDescription NOT LIKE \'%<%\' AND
				d.ClPhGrDescription IS NOT NULL
		')->execute();

		# далее надо преобразовать остальные по регуляркам
		$count = $em->createQuery('
			SELECT COUNT(d.DocumentID)
			FROM VidalDrugBundle:Document d
			WHERE d.CountryEditionCode = \'RUS\' AND
			 	(d.ClPhGrDescription LIKE \'%<%\' OR d.ClPhGrDescription IS NULL)
		')->getSingleScalarResult();

		$query = $em->createQuery('
			SELECT d.DocumentID, d.ClPhGrDescription
			FROM VidalDrugBundle:Document d
			WHERE d.CountryEditionCode = \'RUS\' AND
				(d.ClPhGrDescription LIKE \'%<%\' OR d.ClPhGrDescription IS NULL)
		');

		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Document d
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

		$output->writeln('+++ vidal:document_clphgrname completed!');
	}
}
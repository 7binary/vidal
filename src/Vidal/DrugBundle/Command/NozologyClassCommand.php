<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда генерации названий для автодополнения поисковой строки
 *
 * @package Vidal\DrugBundle\Command
 */
class NozologyClassCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:nozology_class')
			->setDescription('Fills nozology with "class" field and removes tags');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:nozology_class started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		# находим заболевания с тегами <sub> или <sup>
		$nozologies = $em->createQuery('
			SELECT n.NozologyCode, n.Name
			FROM VidalDrugBundle:Nozology n
			WHERE n.Name LIKE \'%<sup%\' OR n.Name LIKE \'%<sub%\'
		')->getResult();

		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Nozology n
			SET n.Name = :Name
			WHERE n.NozologyCode = :NozologyCode
		');

		for ($i = 0; $i < count($nozologies); $i++) {
			$origName = $nozologies[$i]['Name'];
			$Name     = preg_replace('/<\\/?su(p|b)>/i', '', $origName);
			$updateQuery
				->setParameters(array('Name' => $Name, 'NozologyCode' => $nozologies[$i]['NozologyCode']))
				->execute();
		}

		# находим заболевания с тегами <br>
		$nozologies = $em->createQuery('
			SELECT n.NozologyCode, n.Name
			FROM VidalDrugBundle:Nozology n
			WHERE n.Name LIKE \'%<br%\'
		')->getResult();

		if (empty($nozologies)) {
			$output->writeln('+++ vidal:nozology_class has not found nozologies to update!');

			return 0;
		}

		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:Nozology n
			SET n.Class = :Class, n.Name = :Name
			WHERE n.NozologyCode = :NozologyCode
		');

		for ($i = 0; $i < count($nozologies); $i++) {
			$origName = $nozologies[$i]['Name'];
			$Class    = mb_substr($origName, 0, mb_strpos($origName, '<'));
			$Name     = mb_substr($origName, mb_strpos($origName, '>') + 1);

			$updateQuery->setParameters(array(
				'Class'        => $Class,
				'Name'         => $Name,
				'NozologyCode' => $nozologies[$i]['NozologyCode'],
			))->execute();
		}

		$output->writeln("+++ vidal:nozology_class updated $i nozologies!");
	}
}
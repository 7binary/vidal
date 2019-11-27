<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NozologyParentCommand extends ContainerAwareCommand
{
	protected $couples = array();

	protected function configure()
	{
		$this->setName('vidal:nozology_parent');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:nozology_parent started');

		$file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'nosology.json';
		$data = json_decode(file_get_contents($file), true);

		$this->fetch($data);
		$em    = $this->getContainer()->get('doctrine')->getManager('drug');
		$query = $em->createQuery('
			UPDATE VidalDrugBundle:Nozology n
			SET n.parent = :parent
			WHERE n.NozologyCode IN (:codes)
		');

		foreach ($this->couples as $parent => $codes) {
			$query->setParameter('parent', $parent)->setParameter('codes', $codes)->execute();
		}

		$output->writeln('+++ vidal:nozology_parent completed');
	}

	private function fetch($data, $ParentCode = null)
	{
		foreach ($data as $i => $nozology) {
			if (!empty($nozology['children'])) {
				$this->fetch($nozology['children'], $nozology['nc']);
			}

			if ($ParentCode) {
				empty($this->couples[$ParentCode])
					? $this->couples[$ParentCode] = array($nozology['nc'])
					: $this->couples[$ParentCode][] = $nozology['nc'];
			}
		}
	}
}
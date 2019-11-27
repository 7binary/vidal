<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class KfuParentCommand extends ContainerAwareCommand
{
	protected $couples = array();

	protected function configure()
	{
		$this->setName('vidal:kfu_parent');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:kfu_parent started');

		$file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'kfu.json';
		$data = json_decode(file_get_contents($file), true);

		$this->fetch($data);

		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$updateQuery = $em->createQuery('
			UPDATE VidalDrugBundle:ClinicoPhPointers c
			SET c.parent = :parent
			WHERE c.ClPhPointerID IN (:ids)
		');

		$selectIdsQuery = $em->createQuery('
			SELECT c.ClPhPointerID
			FROM VidalDrugBundle:ClinicoPhPointers c
			WHERE c.Code IN (:codes)
		');

		$selectIdQuery = $em->createQuery('
			SELECT c.ClPhPointerID
			FROM VidalDrugBundle:ClinicoPhPointers c
			WHERE c.Code = :code
		');

		foreach ($this->couples as $code => $codes) {
			$ids      = array();
			$pointers = $selectIdsQuery->setParameter('codes', $codes)->getResult();

			foreach ($pointers as $pointer) {
				$ids[] = $pointer['ClPhPointerID'];
			}

			$parent = $selectIdQuery->setParameter('code', $code)->getSingleScalarResult();

			$updateQuery
				->setParameter('parent', $parent)
				->setParameter('ids', $ids)
				->execute();
		}

		$output->writeln('+++ vidal:kfu_parent completed');
	}

	private function fetch($data, $id = null)
	{
		foreach ($data as $i => $nozology) {
			if (!empty($nozology['children'])) {
				$this->fetch($nozology['children'], $nozology['id']);
			}

			if ($id) {
				empty($this->couples[$id])
					? $this->couples[$id] = array($nozology['id'])
					: $this->couples[$id][] = $nozology['id'];
			}
		}
	}
}
<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorNozologyCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:generator_nozology');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:generator_nozology started');

		$em         = $this->getContainer()->get('doctrine')->getManager('drug');
		$nozologies = $em->getRepository('VidalDrugBundle:Nozology')->jsonForTree();
		$finds      = array();

		$i = 0;
		foreach ($nozologies as $code => &$n) {
			$n['i']    = $i;
			$n['code'] = $n['id'];
			$finds[]   = $n;
			$i++;
		}

		# надо сгруппировать по родителю (запихпуть в list родителя дочерние)
		for ($i = 4; $i > 0; $i--) {
			foreach ($nozologies as $code => &$nozology) {
				if ($nozology['Level'] == $i) {
					# надо найти родителя
					$prev  = false;
					$minus = 1;
					while (!$prev) {
						$prevIndex = $nozology['i'] - $minus;
						if ($finds[$prevIndex]['Level'] < $nozology['Level']) {
							$prev = $finds[$prevIndex];
						}
						$minus++;
					}
					$prevCode                            = $prev['id'];
					$nozologies[$prevCode]['children'][] = $nozology;
				}
			}
		}

		# надо удалить промежуточные, ненужны поля
		foreach ($nozologies as $code => &$nozology) {
			unset($nozology['i']);
		}

		# надо взять только верхний уровень
		$grouped = array();

		foreach ($nozologies as $code => &$nozology) {
			if ($nozology['Level'] == 0) {
				$grouped[$code] = $nozology;
			}
		}

		$file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'nosology.json';
		file_put_contents($file, json_encode($grouped));

		//		$json = json_decode(file_get_contents($file), true);
		//		$data = $json['16463']['children'];

		$output->writeln('+++ vidal:generator_nozology completed');
	}
}
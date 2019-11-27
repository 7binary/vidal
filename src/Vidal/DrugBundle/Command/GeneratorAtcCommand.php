<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GeneratorAtcCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:generator_atc');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
        AllCommand::$db_update = true;
		$output->writeln('--- vidal:generator_atc started');
		$em = $this->getContainer()->get('doctrine')->getManager('drug');

		$atcCodes   = $em->getRepository('VidalDrugBundle:ATC')->jsonForTree();
		$atcGrouped = array();

		# надо сгруппировать по родителю
		for ($i = 8; $i > 1; $i--) {
			foreach ($atcCodes as $code => &$atc) {
				$atc['code'] = $code;
				if (strlen($code) == $i && isset($atc['ParentATCCode'])) {
					$key = $atc['ParentATCCode'];
					unset($atc['ParentATCCode']);
					$atcCodes[$key]['children'][] = $atc;
					$atcCodes[$key]['expanded']   = false;
				}
			}
		}

		# взять только первый уровень [A, B, C]
		foreach ($atcCodes as $code => $atc) {
			if (strlen($code) == 1) {
				$atcGrouped[$code] = $atc;
			}
		}

		$file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'atc.json';
		file_put_contents($file, json_encode($atcGrouped));

		$output->writeln('+++ vidal:generator_atc completed');
	}
}
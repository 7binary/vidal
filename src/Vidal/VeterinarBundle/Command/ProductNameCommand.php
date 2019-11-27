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
class ProductNameCommand extends ContainerAwareCommand
{
	private $letters = array('a' => 'А', 'b' => 'Б', 'v' => 'В', 'g' => 'Г', 'd' => 'Д', 'e' => 'Е', 'z' => 'З', 'i' => 'И', 'j' => 'Й', 'k' => 'К', 'l' => 'Л', 'm' => 'М', 'n' => 'Н', 'o' => 'О', 'p' => 'П', 'r' => 'Р', 's' => 'С', 't' => 'Т', 'u' => 'У', 'f' => 'Ф', 'h' => 'Х', 'c' => 'Ц', 'ch' => 'Ч', 'sh' => 'Ш', 'je' => 'Э', 'ju' => 'Ю', '8' => '8');

	protected function configure()
	{
		$this->setName('veterinar:product_name')
			->setDescription('Adds Product.Name');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('--- veterinar:product_name started');

		$em = $this->getContainer()->get('doctrine')->getManager('veterinar');
		$pdo = $em->getConnection();

		# Product.Name
		$pdo->prepare("UPDATE product SET Name = REPLACE(EngName,'<SUP>','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'</SUP>','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'<SUB>','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'</SUB>','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'<BR/>','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'<BR />','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'&reg;','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'&amp;','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'&trade;','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'&alpha;','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'&beta;','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'&plusmn;','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'(','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,')','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,',','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,' - ','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'  ','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,' ','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'__','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'/','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'.','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'+','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'%','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'\"','')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,\"'\",'')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'---','-')")->execute();
		$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'--','-')")->execute();

		foreach (array_flip($this->letters) as $ru => $eng) {
			$pdo->prepare("UPDATE product SET Name = REPLACE(Name,'$ru','$eng')")->execute();
		}

		$pdo->prepare("UPDATE product SET Name = LOWER(Name)")->execute();

		# Product.RusName2
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName,'<SUP>','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</SUP>','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<SUB>','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</SUB>','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<BR/>','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<BR />','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&reg;','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&amp;','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&trade;','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&alpha;','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&beta;','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&plusmn;','')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'  ',' ')")->execute();
		$pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'%','')")->execute();

		$output->writeln("+++ veterinar:product_name completed!");
	}
}
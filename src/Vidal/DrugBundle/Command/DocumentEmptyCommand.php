<?php
namespace Vidal\DrugBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentEmptyCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('vidal:document_empty');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ini_set('memory_limit', -1);
		$output->writeln('--- vidal:document_empty started');

		$em = $this->getContainer()->get('doctrine')->getManager('drug');
		$pdo = $em->getConnection();
		$fields = explode(' ', 'CompaniesDescription ClPhGrDescription ClPhGrName PhInfluence PhKinetics Dosage OverDosage Interaction Lactation SideEffects StorageCondition Indication ContraIndication SpecialInstruction RenalInsuf HepatoInsuf PharmDelivery ElderlyInsuf ChildInsuf');

		foreach ($fields as $field) {
			$this->cleanField($field, $pdo, $output);
		}

		$output->writeln("+++ vidal:document_empty completed!");
	}

	private function cleanField($field, $pdo)
	{
		$stmt = $pdo->prepare("UPDATE document SET $field = NULL WHERE $field IN ('<P><P/>', '<P> <P/>');");
		$stmt->execute();
	}
}
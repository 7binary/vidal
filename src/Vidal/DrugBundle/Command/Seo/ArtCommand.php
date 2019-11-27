<?php

namespace Vidal\DrugBundle\Command\Seo;

use Vidal\MainBundle\Command\Seo\SslReplace;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArtCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:seo:art')
            ->addArgument('id', InputArgument::OPTIONAL, 'Art id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('<info>--- vidal:seo:art started</info>');
        $container = $this->getContainer();
        $artId = $input->getArgument('id');

        /** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getEntityManager('drug');

        $pdo = $emDrug->getConnection();

        $articles = empty($artId)
            ? $emDrug->getRepository("VidalDrugBundle:Art")->findAll()
            : array($emDrug->getRepository("VidalDrugBundle:Art")->findOneById($artId));

        $updatedItems = 0;
        if (!empty($articles)) {
            $total = count($articles);
            foreach ($articles as $i => $article) {
                $baseText = $article->getBody();
                $newText = $baseText;
                foreach(SslReplace::TO_SSL as $link) {
                    $linkWithSsl = str_replace("http:", "https:", $link);
                    $newText = str_replace($link, $linkWithSsl, $newText);
                }

                if ($newText != $baseText) {

                    $updatedItems++;
                    $id = $article->getId();
                    $pdo->prepare("UPDATE art SET body = ? WHERE id = ?")->execute(array($newText, $id));
                }
            }
        }

        $output->writeln("<comment>updated {$updatedItems} items</comment>");
        $output->writeln('<info>--- vidal:seo:art finished</info>');
    }
}
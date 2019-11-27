<?php

namespace Vidal\MainBundle\Command\Seo;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AboutCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:seo:about')
            ->addArgument('id', InputArgument::OPTIONAL, 'About id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('<info>--- vidal:seo:about started</info>');
        $container = $this->getContainer();
        $artId = $input->getArgument('id');

        /** @var EntityManager $emDefault */
        $emDefault = $container->get('doctrine')->getEntityManager('default');

        $pdo = $emDefault->getConnection();

        $articles = empty($artId)
            ? $emDefault->getRepository("VidalMainBundle:About")->findAll()
            : array($emDefault->getRepository("VidalMainBundle:About")->findOneById($artId));

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
                    $pdo->prepare("UPDATE about SET body = ? WHERE id = ?")->execute(array($newText, $id));
                }
            }
        }

        $output->writeln("<comment>updated {$updatedItems} items</comment>");
        $output->writeln('<info>--- vidal:seo:about finished</info>');
    }
}
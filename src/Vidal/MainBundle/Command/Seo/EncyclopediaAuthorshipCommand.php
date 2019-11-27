<?php

namespace Vidal\MainBundle\Command\Seo;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EncyclopediaAuthorshipCommand extends ContainerAwareCommand
{
    const BIG_ARTICLE_LEN = 5500;

    protected function configure()
    {
        $this->setName('vidal:seo:encyclopedia-authorship')
            ->addArgument('id', InputArgument::OPTIONAL, 'About id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $authors[0] = 'врач, к. м. н., Юдинцева М. С., <a href="mailto:m.yudintseva@vidal.ru">m.yudintseva@vidal.ru</a>';
        $authors[1] = 'врач, к. м. н., Толмачева Е. А., <a href="mailto:e.tolmacheva@vidal.ru">e.tolmacheva@vidal.ru</a>';
        $authors[2] = 'врач, научный директор АО "Видаль Рус", Жучкова Т. В., <a href="mailto:t.zhutchkova@vidal.ru">t.zhutchkova@vidal.ru</a>';

        ini_set('memory_limit', -1);
        $output->writeln('<info>--- vidal:seo:encyclopedia-authorship started</info>');
        $container = $this->getContainer();
        $artId = $input->getArgument('id');

        /** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getEntityManager('drug');

        $pdo = $emDrug->getConnection();

        $articles = empty($artId)
            ? $emDrug->getRepository("VidalDrugBundle:Article")->findAll()
            : array($emDrug->getRepository("VidalDrugBundle:Article")->findOneById($artId));

        $itemsWithOutExport = 0;
        if (!empty($articles)) {
            $total = count($articles);
            foreach ($articles as $i => $article) {
                $disableExport = $article->getDisableExport();
                if(!$disableExport) {
                    $itemsWithOutExport++;
                }
            }
        }

        $oneTotalCount = round(($itemsWithOutExport * 60)/100);
        $twoTotalCount = round(($itemsWithOutExport * 25)/100);
        $threeTotalCount = round(($itemsWithOutExport * 15)/100);

        $oneItems = 0;
        $twoItems = 0;
        $threeItems = 0;

        $updatedItems = 0;
        if (!empty($articles)) {
            $total = count($articles);
            foreach ($articles as $i => $article) {
                $id = $article->getId();
                $body = $article->getBody();
                $disableExport = $article->getDisableExport();
                if(!$disableExport) {
                    $firstItem = rand(0, 2);
                    $authorsText = '<p style="text-align: right;"><em>Автор: '.$authors[$firstItem].'</em></p>';

                    if($oneItems < $oneTotalCount) {
                        $authorsText = '<p style="text-align: right;"><em>Автор: '.$authors[$firstItem].'</em></p>';
                        $oneItems++;
                    }

                    if(
                        ($twoItems < $twoTotalCount && mb_strlen($body) > self::BIG_ARTICLE_LEN) or 
                        ($oneItems > $oneTotalCount && $twoItems < $twoTotalCount)
                    ) {
                        $authorsTmp = $authors;
                        unset($authorsTmp[$firstItem]);
                        $authorsText = '<p style="text-align: right;"><em>Авторы: ';
                        $firstIteration = true;
                        foreach($authorsTmp as $authorTmp) {
                            $authorsText .= $authorTmp;
                            if($firstIteration) {
                                $authorsText.="<br/>";
                            }
                            $firstIteration = false;
                        }

                        $authorsText .='</em></p>';
                        $twoItems++;
                    }

                    if(
                        ($threeItems < $threeTotalCount && mb_strlen($body) > self::BIG_ARTICLE_LEN) or 
                        ($oneItems > $oneTotalCount && $threeItems < $threeTotalCount)
                    ) {
                        $authorsText = '<p style="text-align: right;"><em>Авторы: '.$authors[0] .'</br>'.$authors[1].'<br/>'.$authors[2].'</em></p>';
                        $threeItems++;
                    }

                    $pdo->prepare("UPDATE article SET authors = ? WHERE id = ?")->execute(array($authorsText, $id));
                    $updatedItems++;
                }
            }
        }

        $output->writeln("<comment>With 1 author {$oneItems} items</comment>");
        $output->writeln("<comment>With 2 author {$twoItems} items</comment>");
        $output->writeln("<comment>With 3 author {$threeItems} items</comment>");
        $output->writeln("<comment>updated {$updatedItems} items</comment>");
        $output->writeln('<info>--- vidal:seo:encyclopedia-authorship finished</info>');
    }
}
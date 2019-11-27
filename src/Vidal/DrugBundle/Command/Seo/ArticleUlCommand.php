<?php

namespace Vidal\DrugBundle\Command\Seo;

use Vidal\MainBundle\Command\Seo\SslReplace;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ArticleUlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:seo:article_ul')
            ->addArgument('id', InputArgument::OPTIONAL, 'Article id')
            ->setDescription('Меняет <p> на <ul> содержании статьи');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Документ 250
        //369
        ini_set('memory_limit', -1);
        $output->writeln('<info>--- vidal:seo:article_ul started</info>');
        $container = $this->getContainer();
        $documentId = $input->getArgument('id');

        /** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getEntityManager('drug');

        $pdo = $emDrug->getConnection();

        $documents = empty($documentId)
            ? $emDrug->getRepository("VidalDrugBundle:Article")->findAll()
            : array($emDrug->getRepository("VidalDrugBundle:Article")->findOneById($documentId));

        $updatedItems = [];
        if (!empty($documents)) {
            $total = count($documents);
            foreach ($documents as $i => $document) {
                $id = $document->getId();

                // Описание, Форма выпуска, состав и упаковка
                $content = $document->getBody();
                $contentNew = $this->findUl($content, "p");
                $contentNew = $this->findUl($contentNew, "div");

                if ($content != $contentNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE article SET bodyLinked = ? WHERE id = ?")->execute(
                        array($contentNew, $id)
                    );
                }
            }
        }

        $updatedItemsCount = count($updatedItems);
        $output->writeln("<comment>updated {$updatedItemsCount} items</comment>");
        $output->writeln('<info>--- vidal:seo:article_ul finished</info>');
    }

    /**
     * Ищет <p> и заменяет <ul> в тексте
     * 
     * @access protected
     * @param string $text
     * @return string
     */
    protected function findUl($text, $tag = "p")
    {
        $openTag = "<$tag>";
        $closeTag = "</$tag>";
        $textNew = str_replace(strtoupper($openTag) , $openTag, $text);
        $textNew = str_replace(strtoupper($closeTag), $closeTag, $textNew);
        $textNew = str_replace("$openTag&nbsp;-", "$openTag- ", $textNew);
        $textNew = str_replace("$openTag<strong>-</strong>", "$openTag- ", $textNew);
        $textNew = str_replace("$openTag<strong>- </strong>", "$openTag- ", $textNew);
        $textNew = str_replace("$openTag&mdash;", "$openTag- ", $textNew);
        $hasList = false;


        if (strpos($textNew, "$openTag- ") !== false) {
            $hasList = true;
        }

        if($hasList) {
            preg_match_all("/$openTag- (.*?)<\/$tag>/s", $textNew, $matches);
            if(isset($matches[0]) && count($matches[0]) > 0) {
                foreach($matches[0] as $match) {
                    $matchNew = str_replace("$openTag- ", "<li>", $match);
                    $matchNew = str_replace($closeTag, "</li>", $matchNew);

                    $textNew = str_replace($match, $matchNew, $textNew);
                }
            }


            $listLi = explode("</li>", $textNew);
            foreach($listLi as $index => $li) {
                if($index >0 && isset($listLi[$index-1])) {
                    if (strpos($listLi[$index-1], '<li>') !== false) {
                        if (strpos($li, $openTag) !== false) {
                            $liNew = "</ul>".$li;
                            $textNew = str_replace($li, $liNew, $textNew);
                        }
                    }
                }

                if (strpos($li, $openTag) !== false) {
                    $liNew = str_replace('<li>', '<ul><li>', $li);
                    $textNew = str_replace($li, $liNew, $textNew);
                }
            }
            $textNew = $this->closetags($textNew);

            if (strpos($textNew, '<li>') !== false) {
                if (strpos($textNew, '<ul>') === false) {
                    $textNew  ="<ul>".$textNew."</ol>";
                }
            }
        }

        return $textNew;
    }

    /**
     * Закрывает теги в html
     * 
     * @access protected
     * @param string $html
     * @return string
     */
    protected function closetags($html)
    {
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    }
}
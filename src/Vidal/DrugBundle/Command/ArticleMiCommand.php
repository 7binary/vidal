<?php
namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\Article;
use Vidal\DrugBundle\Entity\ArticleRubrique;
use Vidal\DrugBundle\Entity\ArtType;

class ArticleMiCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:article_mi');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:article_mi started');

        $this->moveEncyclopedia();

        $this->moveArts();

        $output->writeln("+++ vidal:article_mi completed!");
    }

    private function moveEncyclopedia()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');

        $rubriqueTitle = 'Офтальмологические средства';

        /** @var ArticleRubrique $newRubrique */
        $newRubrique = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByTitle($rubriqueTitle);
        /** @var ArticleRubrique $miRubrique */
        $miRubrique = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByTitle('Медицинские изделия');
        /** @var ArticleRubrique $r1 */
        $r1 = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByRubrique('sredstva-gigieny');
        /** @var ArticleRubrique $r2 */
        $r2 = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByRubrique('sredstva-dlya-okazaniya-pervoy-pomoshchi');

        if ($newRubrique == null) {
            $newRubrique = new ArticleRubrique();
            $newRubrique->setTitle('Офтальмологические средства');
            $newRubrique->setRubrique('oftalmologicheskie-sredstva');
            $em->persist($newRubrique);
            $em->flush($newRubrique);
        }

        foreach ($miRubrique->getArticles() as $article) {
            /** @var Article $article */
            $article->setRubrique($newRubrique);
        }

        foreach ($r1->getArticles() as $article) {
            /** @var Article $article */
            $article->setRubrique($miRubrique);
        }

        foreach ($r2->getArticles() as $article) {
            /** @var Article $article */
            $article->setRubrique($miRubrique);
        }

        $r1->setEnabled(false);
        $r2->setEnabled(false);

        $em->flush();
    }

    private function moveArts()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');
        /** @var ArticleRubrique $miRubrique */
        $miRubrique = $em->getRepository('VidalDrugBundle:ArticleRubrique')->findOneByTitle('Медицинские изделия');

        /** @var ArtType[] $types */
        $types = $em->getRepository('VidalDrugBundle:ArtType')->findByIds(array(31, 30, 28));

        foreach ($types as $type) {
            foreach ($type->getArts() as $art) {
                /** @var Art $art */
                $article = new Article();
                $article->setTitle($art->getTitle());
                $article->setRubrique($miRubrique);
                $article->setAnnounce($art->getAnnounce());
                $article->setBody($art->getBody());
                $article->setLink($art->getLink());
                $article->setDate($art->getDate());
                $article->setCreated($art->getCreated());
                $article->setUpdated($art->getUpdated());

                $em->persist($article);
                $em->flush($article);

                $em->remove($art);
                $em->flush();
            }

            $type->setEnabled(false);
            $em->flush($type);
        }
    }
}
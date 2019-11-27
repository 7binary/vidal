<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use PHPWord_Style_Font;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\ArtCategory;
use Vidal\DrugBundle\Entity\ArtRubrique;

class GeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:generator');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:generator started');

        $container = $this->getContainer();

		/** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getManager('drug');
        $emVet = $container->get('doctrine')->getManager('veterinar');
        $emDefault = $container->get('doctrine')->getManager();
        $webRoot = $container->get('kernel')->getRootDir() . "/../web";

        # статьи энциклопедии
		/** @var Art[] $articles */
        $articles = $emDrug->createQuery('
			SELECT a
			FROM VidalDrugBundle:Art a
			JOIN a.rubrique r WITH r.enabled = 1
			LEFT JOIN a.type t
			LEFT JOIN a.category c
			WHERE a.enabled = TRUE
				AND (t.id IS NULL OR t.enabled = 1)
				AND (c.id IS NULL OR c.enabled = 1)
			ORDER BY r.title ASC, t.title ASC, c.title ASC
		')->getResult();

		$grouped = array();

		foreach ($articles as $a) {
			/** @var ArtRubrique $rubrique */
			$rubrique = $a->getRubrique();
			$rTitle = mb_strtoupper($rubrique->getTitle(), 'utf-8');
			if (!isset($grouped[$rTitle])) {
				$grouped[$rTitle] = array(
					'types' => array(),
					'arts' => array(),
				);
			}

			//"{{ path('art', {'url':art.rubriqueUrl ~ '/' ~ (art.typeUrl ? art.typeUrl ~ '/') ~ (art.categoryUrl ? art.categoryUrl ~ '/') ~ art.link ~ '~' ~ art.id }) }}">{{ art.title|raw }}</a>
			# генерация URL
			$urlParts = array($rubrique->getUrl());
			if ($type = $a->getType()) {
				$urlParts[] = $type->getUrl();
			}
			if ($category = $a->getCategory()) {
				$urlParts[] = $category->getUrl();
			}
			$urlParts[] = $a->getLink();
			$url = 'https://www.vidal.ru' . $container->get('router')->generate('art', array(
				'url' => implode('/', $urlParts)
			));

			if ($type) {
				$tTitle = $type->getTitle();
				if (empty($grouped[$rTitle]['types'][$tTitle])) {
					$grouped[$rTitle]['types'][$tTitle] = array(
						'categories' => array(),
						'arts' => array(),
					);
				}

				if ($category) {
					$cTitle = $category->getTitle();
					if (empty($grouped[$rTitle]['types'][$tTitle]['categories'][$cTitle])) {
						$grouped[$rTitle]['types'][$tTitle]['categories'][$cTitle] = array(
							'arts' => array(),
						);
					}
					$grouped[$rTitle]['types'][$tTitle]['categories'][$cTitle]['arts'][] = array(
						'title' => $a->getTitle(),
						'url' => $url,
						'created' => $a->getDate()->format('d.m.Y'),
					);
				}
				else {
					$grouped[$rTitle]['types'][$tTitle]['arts'][] = array(
						'title' => $a->getTitle(),
						'url' => $url,
						'created' => $a->getDate()->format('d.m.Y'),
					);
				}
			}
			else {
				$grouped[$rTitle]['arts'][] = array(
					'title' => $a->getTitle(),
					'url' => $url,
					'created' => $a->getDate()->format('d.m.Y'),
				);
			}
		}

		$text = array();

		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$section = $phpWord->addSection();

		$fontStyle = new \PhpOffice\PhpWord\Style\Font();
		$fontStyle->setBold(true);
		$fontStyle->setName('Tahoma');
		$fontStyle->setSize(13);

		$boldStyle = 'boldStyle';
		$phpWord->addFontStyle(
			$boldStyle,
			array('name' => 'Tahoma', 'size' => 13, 'color' => '1B2232', 'bold' => true)
		);

		$italicStyle = 'italicStyle';
		$phpWord->addFontStyle(
			$italicStyle,
			array('name' => 'Tahoma', 'size' => 13, 'color' => '1B2232', 'italic' => true)
		);

		foreach ($grouped as $rTitle => $items) {
			$text[] = '';
			$text[] = '. ' . $rTitle;
			$section->addText('Раздел: ' . $rTitle, $boldStyle);

			foreach ($items['arts'] as $art) {
				$text[] = '';
				$text[] = $art['title'];
				$text[] = $art['url'];
				$text[] = $art['created'];

				\PhpOffice\PhpWord\Shared\Html::addHtml($section, $art['title']);
				$section->addLink($art['url'], $art['url'], array('color' => '0000FF', 'text-decoration' => 'underline'));
				$section->addText($art['created']);
			}

			foreach ($items['types'] as $tTitle => $typeItems) {
				$text[] = '';
				$text[] = '.. ' . mb_strtoupper($tTitle, 'UTF-8');
				$section->addText('Подраздел: ' . $tTitle, $boldStyle);

				foreach ($typeItems['arts'] as $typeArt) {
					$text[] = '';
					$text[] = $typeArt['title'];
					$text[] = $typeArt['url'];
					$text[] = $typeArt['created'];

					\PhpOffice\PhpWord\Shared\Html::addHtml($section, $typeArt['title']);
					$section->addLink($typeArt['url'], $typeArt['url'], array('color' => '0000FF', 'text-decoration' => 'underline'));
					$section->addText($typeArt['created']);
				}

				foreach ($typeItems['categories'] as $cTitle => $categoryItems) {
					$text[] = '';
					$text[] = '... ' . mb_strtoupper($cTitle, 'UTF-8');
					$section->addText('Категория: ' . $cTitle, $italicStyle);

					foreach ($categoryItems['arts'] as $categoryArt) {
						$text[] = '';
						$text[] = $categoryArt['title'];
						$text[] = $categoryArt['url'];
						$text[] = $categoryArt['created'];

						\PhpOffice\PhpWord\Shared\Html::addHtml($section, $categoryArt['title']);
						$section->addLink($categoryArt['url'], $categoryArt['url'], array('color' => '0000FF', 'text-decoration' => 'underline'));
						$section->addText($categoryArt['created']);
					}
				}
			}
		}

		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		$objWriter->save($webRoot . '/arts.docx');

//		$content = implode(PHP_EOL, $text);
//		$fp = fopen($webRoot  . "/arts.txt", "wb");
//		fwrite($fp, $content);
//		fclose($fp);

        $output->writeln('+++ vidal:generator');
    }
}
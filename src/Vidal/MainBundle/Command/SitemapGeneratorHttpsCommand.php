<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Article;
use Vidal\DrugBundle\Entity\Interaction;

class SitemapGeneratorHttpsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:sitemap:generate_https');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:sitemap:generate_https started');

        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager('drug');
        /** @var EntityManager $emVet */
        $emVet = $container->get('doctrine')->getManager('veterinar');
        /** @var EntityManager $emDefault */
        $emDefault = $container->get('doctrine')->getManager();
        $webRoot = $container->get('kernel')->getRootDir() . "/../web";

        ////////////////////////////////////////////
        $urlset = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset2 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset3 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset4 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset5 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset6 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset7 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset8 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset9 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset10 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset20 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset21 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset24 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset25 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset26 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlset27 = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');
        $urlsetTest = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" /><!--?xml version="1.0" encoding="UTF-8"?-->');

        $date = new \DateTime();
        $lastMod = $date->format('Y-m-d');

        $xmlMain = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>
			<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap10.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap11.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap12.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap13.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap14.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap15.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap16.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap17.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap18.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap19.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap20.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap21.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap24.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap25.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap26.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			 <sitemap>
				<loc>https://www.vidal.ru/sitemap27.xml</loc>
				<lastmod>' . $lastMod . '</lastmod>
			 </sitemap>
			</sitemapindex>
		');

        # картинка-1
        $url = $urlset->addChild('url');
        $image = $url->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
        $image->addChild('image:loc', 'https://www.vidal.ru/bundles/vidalmain/images/header.jpg', 'http://www.google.com/schemas/sitemap-image/1.1');
        $image->addChild('image:caption', 'Видаль', 'http://www.google.com/schemas/sitemap-image/1.1');

        # картинка-2
        $url = $urlset->addChild('url');
        $image = $url->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
        $image->addChild('image:loc', 'https://www.vidal.ru/bundles/vidalmain/images/vidal-group.jpg', 'http://www.google.com/schemas/sitemap-image/1.1');
        $image->addChild('image:caption', 'Группа Видаль', 'http://www.google.com/schemas/sitemap-image/1.1');

        # препараты
        $products = $em->createQuery("
            SELECT p.ProductID, p.Name, p.url, p.uri, p.countPictures, p.pictures
            FROM VidalDrugBundle:Product p
            WHERE p.MarketStatusID IN (1,2,7)
                AND p.ProductTypeCode NOT IN ('SUBS')
                AND p.inactive = FALSE
                AND p.IsNotForSite = FALSE
                AND p.ParentID IS NULL
                AND p.MainID IS NULL
        ")->getResult();

        $productsChunked = array_chunk($products, 10000);

        foreach ($productsChunked[0] as $product) {
            $url = $urlset4->addChild('url');
            $loc = "https://www.vidal.ru/drugs/{$product['uri']}";

            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');

            if ($product['countPictures'] >= 1) {
                $url = $urlset24->addChild('url');
                $url->addChild('loc', $loc);
                $pictures = explode('|', $product['pictures']);
                foreach ($pictures as $picture) {
                    $image = $url->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
                    $image->addChild('image:loc', 'https://www.vidal.ru/upload/products/' . $picture, 'http://www.google.com/schemas/sitemap-image/1.1');
                }
            }
        }

        foreach ($productsChunked[1] as $product) {
            $url = $urlset5->addChild('url');
            $loc = "https://www.vidal.ru/drugs/{$product['uri']}";

            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');

            if ($product['countPictures'] >= 1) {
                $url = $urlset25->addChild('url');
                $url->addChild('loc', $loc);
                $pictures = explode('|', $product['pictures']);
                foreach ($pictures as $picture) {
                    $image = $url->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
                    $image->addChild('image:loc', 'https://www.vidal.ru/upload/products/' . $picture, 'http://www.google.com/schemas/sitemap-image/1.1');
                }
            }
        }

        if (!empty($productsChunked[2])) {
            foreach ($productsChunked[2] as $product) {
                $url = $urlset6->addChild('url');
                $loc = "https://www.vidal.ru/drugs/{$product['uri']}";

                $url->addChild('loc', $loc);
                $url->addChild('lastmod', $lastMod);
                $url->addChild('changefreq', 'weekly');
                $url->addChild('priority', '0.8');

                if ($product['countPictures'] >= 1) {
                    $url = $urlset26->addChild('url');
                    $url->addChild('loc', $loc);
                    $pictures = explode('|', $product['pictures']);
                    foreach ($pictures as $picture) {
                        $image = $url->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
                        $image->addChild('image:loc', 'https://www.vidal.ru/upload/products/' . $picture, 'http://www.google.com/schemas/sitemap-image/1.1');
                    }
                }
            }
        }

        # активные вещества
        $molecules = $em->createQuery('
					SELECT m.MoleculeID
					FROM VidalDrugBundle:Molecule m
					JOIN m.documents d
					WHERE m.MoleculeID NOT IN (1144,2203)
				')->getResult();

        $moleculesChuncked = array_chunk($molecules, 10000);

        foreach ($moleculesChuncked[0] as $molecule) {
            $url = $urlset7->addChild('url');
            $loc = "https://www.vidal.ru/drugs/molecule/{$molecule['MoleculeID']}";
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');
        }

        foreach ($moleculesChuncked[1] as $molecule) {
            $url = $urlset8->addChild('url');
            $loc = "https://www.vidal.ru/drugs/molecule/{$molecule['MoleculeID']}";
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');
        }

        # статьи энциклопедии
        /** @var Article[] $articles */
        $articles = $em->createQuery('
            SELECT a
            FROM VidalDrugBundle:Article a
            JOIN a.rubrique r
            WHERE a.enabled = TRUE
        ')->getResult();

        foreach ($articles as $article) {
            if ($article->getRubrique() && $article->getRubrique()->getEnabled() && $article->getEnabled()) {
                $url = $urlset2->addChild('url');
                $rubrique = $article->getRubrique()->getRubrique();
                $link = $article->getLink();
                $loc = "https://www.vidal.ru/encyclopedia/{$rubrique}/{$link}";
                $url->addChild('loc', $loc);
                $url->addChild('lastmod', $lastMod);
                $url->addChild('changefreq', 'daily');
                $url->addChild('priority', '0.8');
            }
        }

        # новости
        $publications = $em->createQuery('
            SELECT p.id, p.enabled, p.link
            FROM VidalDrugBundle:Publication p
            WHERE p.enabled = TRUE
        ')->getResult();

        foreach ($publications as $publication) {
            if ($publication['enabled']) {
                $url = $urlset2->addChild('url');
                $loc = "https://www.vidal.ru/novosti/{$publication['link']}-{$publication['id']}";
                $url->addChild('loc', $loc);
                $url->addChild('lastmod', $lastMod);
                $url->addChild('changefreq', 'daily');
                $url->addChild('priority', '0.8');
            }
        }

        # компании
        $companies = $em->createQuery('
			SELECT c.CompanyID
			FROM VidalDrugBundle:Company c
		')->getResult();

        foreach ($companies as $company) {
            $url = $urlset->addChild('url');
            $loc = "https://www.vidal.ru/drugs/firm/{$company['CompanyID']}";
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');
        }

        # представительства
        $infoPages = $em->createQuery('
			SELECT i.InfoPageID
			FROM VidalDrugBundle:InfoPage i
			WHERE i.countProducts > 0
		')->getResult();

        foreach ($infoPages as $infoPage) {
            $InfoPageID = $infoPage['InfoPageID'];
            $picture = $em->getRepository('VidalDrugBundle:Picture')->findByInfoPageID($InfoPageID);
            $url = $urlset->addChild('url');
            $loc = "https://www.vidal.ru/drugs/company/$InfoPageID";
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.8');

            if ($picture && !empty($picture['path'])) {
                $url = $urlset27->addChild('url');
                $url->addChild('loc', $loc);
                $image = $url->addChild('image:image', null, 'http://www.google.com/schemas/sitemap-image/1.1');
                $image->addChild('image:loc', 'https://www.vidal.ru/upload/companies/' . $picture['path'], 'http://www.google.com/schemas/sitemap-image/1.1');
            }
        }

        # о компании
        $abouts = $emDefault->createQuery('
            SELECT a.url
            FROM VidalMainBundle:About a
            WHERE a.enabled = 1
        ')->getResult();

        foreach ($abouts as $about) {
            $url = $urlset->addChild('url');
            $loc = "https://www.vidal.ru/about/{$about['url']}";
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.9');
        }

        # наши услуги
        $abouts = $emDefault->createQuery('
            SELECT a.url
            FROM VidalMainBundle:AboutService a
            WHERE a.enabled = 1
        ')->getResult();

        foreach ($abouts as $about) {
            $url = $urlset->addChild('url');
            $loc = "https://www.vidal.ru/services/{$about['url']}";
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.9');
        }

        # ВЕТЕРИНАРИЯ - общие разделы
        $urls = array(
            'https://www.vidal.ru/veterinar',
            'https://www.vidal.ru/veterinar/gnp',
            'https://www.vidal.ru/veterinar/proizvoditeli',
            'https://www.vidal.ru/veterinar/predstavitelstvo',
            'https://www.vidal.ru/veterinar/kfu',
            'https://www.vidal.ru/veterinar/podrobno-o-preparate',
        );

        # ВЕТЕРИНАРИЯ - препараты
        $products = $emVet->createQuery('
			SELECT p.ProductID, p.Name
			FROM VidalVeterinarBundle:Product p
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode IN (\'DRUG\',\'GOME\')
				AND p.inactive = FALSE
		')->getResult();

        foreach ($products as $product) {
            $urls[] = "https://www.vidal.ru/veterinar/{$product['Name']}-{$product['ProductID']}";
        }

        # ВЕТЕРИНАРИЯ - вещества
        $molecules = $emVet->createQuery('
			SELECT m.MoleculeID, m.LatName
			FROM VidalVeterinarBundle:Molecule m
		')->getResult();

        foreach ($molecules as $molecule) {
            $name = str_replace(' ', '-', $molecule['LatName']);
            $urls[] = "https://www.vidal.ru/veterinar/molecule/{$name}";
            $urls[] = "https://www.vidal.ru/veterinar/molecule-in/{$name}";
        }

        # ВЕТЕРИНАРИЯ - портфели
        $portfolios = $emVet->createQuery('
			SELECT p.url
			FROM VidalVeterinarBundle:PharmPortfolio p
			WHERE p.enabled = TRUE
		')->getResult();

        foreach ($portfolios as $portfolio) {
            $urls[] = "https://www.vidal.ru/podrobno-o-preparate/{$portfolio['url']}";
        }

        # ВЕТЕРИНАРИЯ - компании
        $companies = $emVet->createQuery('
			SELECT c.Name
			FROM VidalVeterinarBundle:Company c
		')->getResult();

        foreach ($companies as $company) {
            $urls[] = "https://www.vidal.ru/veterinar/proizvoditeli/{$company['Name']}";
        }

        # ВЕТЕРИНАРИЯ - представительства
        $infoPages = $emVet->createQuery('
			SELECT i.Name
			FROM VidalVeterinarBundle:InfoPage i
		')->getResult();

        foreach ($infoPages as $infoPage) {
            $urls[] = "https://www.vidal.ru/veterinar/predstavitelstvo/{$infoPage['Name']}";
        }

        # ВЕТЕРИНАРИЯ - КФУ
        $kfuItems = $emVet->createQuery('
			SELECT p.url
			FROM VidalVeterinarBundle:ClinicoPhPointers p
		')->getResult();

        foreach ($kfuItems as $kfu) {
            $urls[] = "https://www.vidal.ru/veterinar/kfu/{$kfu['url']}";
        }

        # ВЕТЕРИНАРИЯ
        foreach ($urls as $url) {
            $xmlUrl = $urlset9->addChild('url');
            $xmlUrl->loc = htmlspecialchars($url);
            $xmlUrl->lastmod = $lastMod;
            $xmlUrl->changefreq = 'weekly';
            $xmlUrl->priority = '0.8';
        }

        # ТЕСТОВЫЙ ДЛЯ СЕО
        $urls = require('sitemap-test.php');

        foreach ($urls as $url) {
            $xmlUrl = $urlsetTest->addChild('url');
            $xmlUrl->loc = $url;
            $xmlUrl->lastmod = $lastMod;
            $xmlUrl->changefreq = 'daily';
            $xmlUrl->priority = '0.5';
        }

        # ЛЕКАРСТВЕННОЕ ВЗАИМОДЕЙСТВИЕ
        /** @var Interaction[] $interactions */
        $interactions = $em->createQuery('
            SELECT i
            FROM VidalDrugBundle:Interaction i
            ORDER BY i.RusName ASC
		')->getResult();

        $url = $urlset20->addChild('url');
        $loc = "https://www.vidal.ru/drugs/interaction";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        foreach ($interactions as $i) {
            $url = $urlset20->addChild('url');
            $name = mb_strtolower($i->getEngName());
            $loc = "https://www.vidal.ru/drugs/interaction/" . $name;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # НОЗОЛОГИЯ
        $url = $urlset21->addChild('url');
        $loc = "https://www.vidal.ru/drugs/nosology";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        $nosologyCodes = $em->createQuery("SELECT n.Code FROM VidalDrugBundle:Nozology n WHERE n.countProducts > 0 ORDER BY n.Code ASC")->getArrayResult();
        foreach ($nosologyCodes as $nosologyCode) {
            $url = $urlset10->addChild('url');
            $loc = "https://www.vidal.ru/drugs/nosology/" . strtolower($nosologyCode['Code']);
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        $codesN = array('a00-b99', 'c00-d48', 'd50-d89', 'e00-e90', 'f00-f99', 'g00-g99', 'h00-h59', 'h60-h95', 'i00-i99', 'j00-j99', 'k00-k93', 'l00-l99', 'm00-m99', 'n00-n99', 'o00-o99', 'p00-p96', 'q00-q99', 'r00-r99', 's00-t98', 'z00-z99');
        for ($i = 0; $i < count($codesN); $i++) {
            $code = $codesN[$i];
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/nosologyl/" . $code;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # КФУ
        $url = $urlset21->addChild('url');
        $loc = "https://www.vidal.ru/drugs/clinic-pointers";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        $codesKfu = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34');
        for ($i = 0; $i < count($codesKfu); $i++) {
            $code = $codesKfu[$i];
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/clinic-pointers/" . $code;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # ATC
        $url = $urlset21->addChild('url');
        $loc = "https://www.vidal.ru/drugs/atc";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        $atcCodes = $em->createQuery("SELECT a.ATCCode FROM VidalDrugBundle:ATC a WHERE a.countProducts > 0 ORDER BY a.ATCCode ASC")->getArrayResult();
        foreach ($atcCodes as $atcCode) {
            $url = $urlset10->addChild('url');
            $loc = "https://www.vidal.ru/drugs/atc/" . strtolower($atcCode['ATCCode']);
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        $codesAtc = array('a', 'b', 'c', 'd', 'g', 'h', 'j', 'm', 'n', 'p', 'r', 's', 'v');
        for ($i = 0; $i < count($codesAtc); $i++) {
            $code = $codesAtc[$i];
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/atcl/" . $code;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # ФТГ
        $codesMenu = array('А' => 'rus-a', 'Б' => 'rus-b', 'В' => 'rus-v', 'Г' => 'rus-g', 'Д' => 'rus-d',
            'Ж' => 'rus-zh', 'З' => 'rus-z',
            'И' => 'rus-i', 'К' => 'rus-k', 'Л' => 'rus-l', 'М' => 'rus-m', 'Н' => 'rus-n', 'О' => 'rus-o', 'П' => 'rus-p',
            'Р' => 'rus-r', 'С' => 'rus-s', 'Т' => 'rus-t', 'У' => 'rus-u', 'Ф' => 'rus-f',
            'Х' => 'rus-h', 'Ц' => 'rus-c', 'Э' => 'rus-e', 'Я' => 'rus-ya', 'H' => 'h');

        $url = $urlset21->addChild('url');
        $loc = "https://www.vidal.ru/drugs/pharm-groups";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        foreach ($codesMenu as $rusCode => $engCode) {
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/pharm-groups/" . $engCode;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # БУКВЫ компаний и представительств
        $codesMenu = array('А' => 'rus-a', 'Б' => 'rus-b', 'В' => 'rus-v', 'Г' => 'rus-g', 'Д' => 'rus-d', 'Е' => 'rus-e',
            'З' => 'rus-z', 'И' => 'rus-i', 'Й' => 'rus-j', 'К' => 'rus-k', 'Л' => 'rus-l', 'М' => 'rus-m',
            'Н' => 'rus-n', 'О' => 'rus-o', 'П' => 'rus-p',
            'Р' => 'rus-r', 'С' => 'rus-s', 'Т' => 'rus-t', 'У' => 'rus-u', 'Ф' => 'rus-f',
            'Х' => 'rus-h', 'Ц' => 'rus-c', 'Ч' => 'rus-ch', 'Ш' => 'rus-sh', 'Щ' => 'rus-shch',
            'Э' => 'rus-eh', 'Ю' => 'rus-yu', 'Я' => 'rus-ya');
        $codesMenuEn = explode(' ', 'a b c d e f g h i j k l m n o p q r s t u v w x y z 4');

        $url = $urlset21->addChild('url');
        $loc = "https://www.vidal.ru/drugs/companies";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        foreach ($codesMenu as $rusCode => $engCode) {
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/companies/" . $engCode;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        foreach ($codesMenuEn as $engCode) {
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/companies/" . $engCode;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # DISEASE
        $codesMenu = array('А' => 'rus-a', 'Б' => 'rus-b', 'В' => 'rus-v', 'Г' => 'rus-g', 'Д' => 'rug-d',
            'Ж' => 'rus-zh', 'З' => 'rus-z',
            'И' => 'rus-i', 'К' => 'rus-k', 'Л' => 'rus-l', 'М' => 'rus-m', 'Н' => 'rus-n', 'О' => 'rus-o', 'П' => 'rus-p',
            'Р' => 'rus-r', 'С' => 'rus-s', 'Т' => 'rus-t', 'У' => 'rus-u', 'Ф' => 'rus-f',
            'Х' => 'rus-h', 'Ц' => 'rus-c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'shch',
            'Э' => 'rus-e', 'Я' => 'rus-ya');

        $url = $urlset21->addChild('url');
        $loc = "https://www.vidal.ru/drugs/disease";
        $url->addChild('loc', $loc);
        $url->addChild('lastmod', $lastMod);
        $url->addChild('changefreq', 'daily');
        $url->addChild('priority', '0.8');

        foreach ($codesMenu as $rusCode => $engCode) {
            $url = $urlset21->addChild('url');
            $loc = "https://www.vidal.ru/drugs/disease/" . $engCode;
            $url->addChild('loc', $loc);
            $url->addChild('lastmod', $lastMod);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        # запись в файл
        $xmlMain->asXML("{$webRoot}/sitemap.xml");
        $urlset10->asXML("{$webRoot}/sitemap10.xml");
        $urlset->asXML("{$webRoot}/sitemap11.xml");
        # $urlsetTest->asXML("{$webRoot}/sitemap-test1.xml");
        $urlset2->asXML("{$webRoot}/sitemap12.xml");
        $urlset3->asXML("{$webRoot}/sitemap13.xml");
        $urlset4->asXML("{$webRoot}/sitemap14.xml");
        $urlset5->asXML("{$webRoot}/sitemap15.xml");
        $urlset6->asXML("{$webRoot}/sitemap16.xml");
        $urlset7->asXML("{$webRoot}/sitemap17.xml");
        $urlset8->asXML("{$webRoot}/sitemap18.xml");
        $urlset9->asXML("{$webRoot}/sitemap19.xml");
        $urlset20->asXML("{$webRoot}/sitemap20.xml");
        $urlset21->asXML("{$webRoot}/sitemap21.xml");
        $urlset24->asXML("{$webRoot}/sitemap24.xml");
        $urlset25->asXML("{$webRoot}/sitemap25.xml");
        $urlset26->asXML("{$webRoot}/sitemap26.xml");
        $urlset27->asXML("{$webRoot}/sitemap27.xml");

        ///////////////////////////////////////////

        $output->writeln('+++ vidal:sitemap:generate_https completed');
    }
}
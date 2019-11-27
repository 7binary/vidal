<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Publication;

class PublicationLinkCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:publication_link');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:publication_link started');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');

        /** @var Publication[] $publications */
        $publications = $em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Publication p
			ORDER BY p.id
		")->getResult();

        foreach ($publications as $p) {
            $title = $p->getTitle();
            $title = self::ctl_sanitize_title($title);
            $p->setLink($title);
            $em->flush($p);
        }

        $output->writeln("+++ vidal:publication_link completed!");
    }

    protected function generateLink($title)
    {
        $rus = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
            'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
            'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
            'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            '—', '-', '  ', ',', '.', '!', '?', '/', '“', '”'
        );

        $eng = array(
            'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
            'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
            'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
            'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
            'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
            '_', '_', '_', '_', '_', '_', '_', '_', '_', '_'
        );

        $title = str_replace($rus, $eng, $title);
        $title = mb_strtolower($title, 'utf-8');

        return $title;
    }

    public static function ctl_sanitize_title($title)
    {
        $tags = array(
            '<sup>TM</sup>' => '',
            '<sup>&reg;</sup>' => '',
            '<sup>&reg;&nbsp;</sup>' => '',
            '&laquo;' => '',
            '&raquo;' => '',
            '&nbsp;' => ' ',
            '&trade;' => '',
            '&mdash;' => '',
            '<sup>®</sup>' => '',
            '<sup>&reg; </sup>' => '',
            '&reg;' => '',
            '<sup>' => '',
            '</sup>' => '',
        );

        $iso9_table = array(
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G',
            'Ґ' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
            'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'J',
            'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
            'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
            'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
            'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '',
            'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
            'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
            'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'j',
            'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
            'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
            'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh', 'ъ' => '',
            'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        $geo2lat = array(
            'ა' => 'a', 'ბ' => 'b', 'გ' => 'g', 'დ' => 'd', 'ე' => 'e', 'ვ' => 'v',
            'ზ' => 'z', 'თ' => 'th', 'ი' => 'i', 'კ' => 'k', 'ლ' => 'l', 'მ' => 'm',
            'ნ' => 'n', 'ო' => 'o', 'პ' => 'p', 'ჟ' => 'zh', 'რ' => 'r', 'ს' => 's',
            'ტ' => 't', 'უ' => 'u', 'ფ' => 'ph', 'ქ' => 'q', 'ღ' => 'gh', 'ყ' => 'qh',
            'შ' => 'sh', 'ჩ' => 'ch', 'ც' => 'ts', 'ძ' => 'dz', 'წ' => 'ts', 'ჭ' => 'tch',
            'ხ' => 'kh', 'ჯ' => 'j', 'ჰ' => 'h'
        );
        $iso9_table = array_merge($iso9_table, $geo2lat, $tags);

        $title = strtr($title, $iso9_table);

        if (function_exists('iconv')) {
            $title = iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $title);
        }
        $title = preg_replace("/[^A-Za-z0-9'_\-\.]/", '-', $title);
        $title = preg_replace('/\-+/', '-', $title);
        $title = preg_replace('/^-+/', '', $title);
        $title = preg_replace('/-+$/', '', $title);
        $title = mb_strtolower($title, 'utf-8');

        return $title;
    }
}
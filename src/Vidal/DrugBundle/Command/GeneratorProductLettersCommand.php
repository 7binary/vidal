<?php

namespace Vidal\DrugBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Product;

class GeneratorProductLettersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:generator_product_letters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', -1);
        $output->writeln('--- vidal:generator_product_letters started');
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('drug');

        # Ищем буквы, по алфавиту
        /** @var Product[] $products */
        $data = array();
        $nonPrescription = array(true, false);
        $types = array('n', 'p', 'b', 'o');

        foreach ($nonPrescription as $non) {
            foreach ($types as $type) {
                $products = $em->getRepository('VidalDrugBundle:Product')->getProductsByLetter($type, $non);
                $letters = array();
                $lettersEng = array();

                foreach ($products as $p) {
                    $name = $p->getRusName2();
                    $first_letter = mb_strtoupper(mb_substr($name, 0, 1, 'utf-8'), 'utf-8');
                    $first_2_letters = $first_letter . mb_strtolower(mb_substr($name, 1, 1, 'utf-8'), 'utf-8');

                    if (preg_match('/^[A-Z]/i', $first_letter) || preg_match('/^[0-9]/i', $first_letter)) {
                        if (!isset($lettersEng[$first_letter])) {
                            $lettersEng[$first_letter] = array();
                            $lettersEng[$first_letter]['subs'] = array();
                            $lettersEng[$first_letter]['eng'] = true;
                            $lettersEng[$first_letter]['trans'] = mb_strtolower($first_letter, 'utf-8');
                        }

                        if (!isset($lettersEng[$first_letter]['subs'][$first_2_letters])) {
                            $lettersEng[$first_letter]['subs'][$first_2_letters] = mb_strtolower($first_2_letters, 'utf-8');
                        }
                    }
                    else {
                        if (!isset($letters[$first_letter])) {
                            $letters[$first_letter] = array();
                            $letters[$first_letter]['subs'] = array();
                            $letters[$first_letter]['eng'] = false;
                            $letters[$first_letter]['trans'] = 'rus-' . self::trans($first_letter);
                        }

                        if (!isset($letters[$first_letter]['subs'][$first_2_letters])) {
                            $letters[$first_letter]['subs'][$first_2_letters] = 'rus-' . self::trans($first_2_letters);
                        }
                    }
                }
                foreach ($lettersEng as $letter => $letterData) {
                    $letters[$letter] = $letterData;
                }

                $key = $type . ($non ? '-non' : '');
                $data[$key] = $letters;
            }
        }

        $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Generated' . DIRECTORY_SEPARATOR . 'product_letters.json';
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $output->writeln('+++ vidal:generator_product_letters completed');
    }

    public static function transRus($title)
    {
        return 'rus-' . self::trans($title);
    }

    public static function trans($title)
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
        $title = mb_strtolower($title, 'utf-8');

        return $title;
    }
}
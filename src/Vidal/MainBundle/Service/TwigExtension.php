<?php
namespace Vidal\MainBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

class TwigExtension extends \Twig_Extension
{
    protected $container;

    public function __construct(Container $container = null)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'learning_twig_extension';
    }

    /**
     * Return the functions registered as twig extensions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'is_file' => new \Twig_Function_Method($this, 'is_file'),
            'dateFromMinutes' => new \Twig_Function_Method($this, 'dateFromMinutes'),
            'evrikaImg' => new \Twig_Function_Method($this, 'evrikaImg'),
            'formatDate' => new \Twig_Function_Method($this, 'formatDate'),
            'getClass' => new \Twig_Function_Method($this, 'getClass'),
            'groupCompanies' => new \Twig_Function_Method($this, 'groupCompanies'),
            'findInfoPage' => new \Twig_Function_Method($this, 'findInfoPage'),
            'hyphenString' => new \Twig_Function_Method($this, 'hyphenString'),
        );
    }

    /**
     * Дополнительные фильтры
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dateRu', array($this, 'dateRu')),
            new \Twig_SimpleFilter('shortcut', array($this, 'shortcut')),
            new \Twig_SimpleFilter('truncateHtml', array($this, 'truncateHtml')),
            new \Twig_SimpleFilter('dateCreated', array($this, 'dateCreated')),
            new \Twig_SimpleFilter('upperFirst', array($this, 'upperFirst')),
            new \Twig_SimpleFilter('ucwords', array($this, 'ucwords')),
            new \Twig_SimpleFilter('type', array($this, 'type')),
            new \Twig_SimpleFilter('values', array($this, 'values')),
            new \Twig_SimpleFilter('regNumber', array($this, 'regNumber')),
            new \Twig_SimpleFilter('realLength', array($this, 'realLength')),
            new \Twig_SimpleFilter('unique', array($this, 'unique')),
            new \Twig_SimpleFilter('composition', array($this, 'composition')),
            new \Twig_SimpleFilter('relative', array($this, 'relative')),
            new \Twig_SimpleFilter('canonical', array($this, 'canonical')),
            new \Twig_SimpleFilter('explodeList', array($this, 'explodeList')),
            new \Twig_SimpleFilter('jsonDecode', array($this, 'jsonDecode')),
            new \Twig_SimpleFilter('nosology', array($this, 'nosology')),
            new \Twig_SimpleFilter('mb_ucfirst', array($this, 'mb_ucfirst')),
            new \Twig_SimpleFilter('first_lower', array($this, 'first_lower')),
            new \Twig_SimpleFilter('html_entity_decode', array($this, 'htmlEntityDecode')),
            new \Twig_SimpleFilter('alt', array($this, 'alt')),
            new \Twig_SimpleFilter('md5_function', array($this, 'md5_function')),
            new \Twig_SimpleFilter('hyphenString', array($this, 'hyphenString')),
            
        );
    }

    public function hyphenString($name)
    {
        $names = explode("-", $name);
        if(count($names) == 2 ) {
            $name1 = $names[0];
            $name2 = $names[1];

            if(mb_strlen($name2)<4) {
                $name2 = mb_strtoupper($name2);
                $name = $name1."-".$name2;
            }
        }
        return $name;
    }

    /**
     * Вытаскивает и преобразует URL картинки из новостей EVRIKA
     */
    public function evrikaImg($file)
    {
        if ($file == null) {
            return null;
        }
        elseif ($file == 'mynews') {
            return $file;
        }
        $array = unserialize($file);
        return 'http://evrika.ru' . $array['path'];
    }

    /**
     * Проверить из твига наличие файла (слеши из начала и конца убираются)
     *
     * @param string $filename
     * @return bool
     */
    public function is_file($filename)
    {
        return file_exists(trim($filename, '/'));
    }

    public function unique($arr)
    {
        sort($arr);
        return array_unique($arr);
    }

    public function md5_function($str)
    {
        return md5($str);
    }

    public function dateFromMinutes($min)
    {
        $inputSeconds = $min * 60;

        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // extract days
        $days = floor($inputSeconds / $secondsInADay);

        // extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        return (int) $days . 'д ' . (int) $hours . 'ч ' . (int) $minutes . 'м';
    }

    public function formatDate($date, $showYear = true)
    {
        if (!$date) {
            return '';
        }

        $months = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

        return $date->format('d') . ' ' . $months[intval($date->format('m'))] . ' ' . $date->format('Y');
    }

    public function dateRu($date, $fullYear = false)
    {
        if (!$date) {
            return '';
        }

        $months = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        $dateStr = $date->format('d') . '&nbsp;' . $months[intval($date->format('m'))];

        if ($fullYear === true) {
            $dateStr .= '&nbsp;года';
        }
        elseif ($fullYear !== null) {
            $dateStr .= '&nbsp;' . $date->format('Y');
        }

        return $dateStr;
    }

    public function dateCreated($date)
    {
        if (!$date) {
            return '';
        }

        $months = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

        return $date->format('d') . ' ' . $months[intval($date->format('m'))] . ' в ' . $date->format('H:i');
    }

    public function shortcut($str, $max)
    {
        return mb_strlen($str, 'UTF-8') > $max
            ? mb_substr($str, 0, $max, 'UTF-8') . '...'
            : $str;
    }

    public function getClass($object)
    {
        $reflect = new \ReflectionClass($object);

        return $reflect->getShortName();
    }

    public function groupCompanies($companies)
    {
        $grouped = array();

        foreach ($companies as $c) {
            if ($c['ItsMainCompany']) {
                $key = '';
            }
            elseif (!empty($c['CompanyRusNote'])) {
                $key = $c['CompanyRusNote'];
            }
            else {
                $key = 'произведено';
            }

            if (empty($grouped[$key])) {
                $grouped[$key] = array();
            }
            $grouped[$key][] = $c;
        }

        return $grouped;
    }

    public function upperFirst($string, $encoding = 'utf-8')
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    public function ucwords($str)
    {
        return mb_convert_case($str, MB_CASE_TITLE, 'utf-8');
    }

    public function type($object)
    {
        $reflect = new \ReflectionClass($object);

        return $reflect->getShortName();
    }

    public function values($array)
    {
        return array_values($array);
    }

    public function regNumber($str)
    {
        if (strlen($str) <= 18) {
            return $str;
        }

        return substr($str, 0, 18) . ' ' . substr($str, 18);
    }

    function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    }
                    else {
                        if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                            // delete tag from $open_tags list
                            $pos = array_search($tag_matchings[1], $open_tags);
                            if ($pos !== false) {
                                unset($open_tags[$pos]);
                            }
                            // if tag is an opening tag
                        }
                        else {
                            if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                                // add tag to the beginning of $open_tags list
                                array_unshift($open_tags, strtolower($tag_matchings[1]));
                            }
                        }
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            }
                            else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                }
                else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        }
        else {
            if (strlen($text) <= $length) {
                return $text;
            }
            else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= ' ' . $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    }

    public function realLength($text)
    {
        return mb_strlen(strip_tags($text), 'UTF-8');
    }

    public function composition($text)
    {
        $text = str_replace('eps|', '', $text);
        $text = str_replace('tif|', '', $text);
        $text = preg_replace('|(<IMG.*?>)(.*?)\.(.*?)(</IMG>)|iu', '<img src="/upload/products_extra/${2}.png"/>', $text);
        $text = preg_replace('|<p>(img_.*?)\.(.*?)</p>|iu', '<p><img src="/upload/products_extra/${1}.png"/></p>', $text);
        //$text = preg_replace('|(img_.*?)\.([^<]+)|iu', '<img src="/upload/products_extra/${1}.png"/>', $text);

        return $text;
    }

    public function relative($text)
    {
        $text = str_replace("http://www.vidal.ru/", "/", $text);
        $text = str_replace("https://www.vidal.ru/", "/", $text);

        return $text;
    }

    public function jsonDecode($json)
    {
        return json_decode($json);
    }

    public function nosology($text)
    {
        try {
            $parts = explode('(', $text);
            $output = $this->mb_ucfirst($parts[0]);
            $output .= '(' . mb_strtoupper($parts[1], 'utf-8');

            return $output;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function alt($text)
    {
        $text = strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $text = trim(str_replace(explode(' ', '® ™'), '', $text));
        $text = $this->mb_ucfirst($text);
        $text .= ' инструкция по применению';

        return $text;
    }

    public function mb_ucfirst($text, $encoding='utf-8')
    {
        return mb_strtoupper(mb_substr($text, 0, 1, $encoding), $encoding)
            . mb_strtolower(mb_substr($text, 1, null, $encoding), $encoding);
    }

    public function first_lower($text, $encoding='utf-8')
    {
        return mb_strtolower(mb_substr($text, 0, 1, $encoding), $encoding);
    }

    public function canonical($url)
    {
        return strtok($url, '?');
    }

    public function explodeList($str)
    {
        if (empty($str)) {
            return array();
        }
        $itemsRaw = explode(';', $str);
        $items = array();
        foreach ($itemsRaw as $item) {
            $items[] = trim($item);
        }

        return $items;
    }

    public function htmlEntityDecode($str)
    {
        return html_entity_decode($str);
    }

    public function findInfoPage($id)
    {
        if ($this->container) {
            /** @var EntityManager $em */
            $emDrug = $this->container->get('doctrine')->getManager('drug');
            return $emDrug->getRepository("VidalDrugBundle:InfoPage")->findOneByInfoPageID($id);
        }

        return null;
    }
}
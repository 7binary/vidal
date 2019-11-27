<?php

namespace Vidal\MainBundle\Market;

class MarketParser2{
    protected $url = 'http://vidal:3L29y4@smacs.ru/exchange/price';
    protected $drugUrl = 'http://www.piluli.ru/product';
    protected $cachetime = 6000;
    protected $cachefile = 'cached2.xml';
    protected $xml;
    protected $arUrl;

    /** Подгружает XML */
    public function __construct($masUrl){

        $this->cachefile = dirname(dirname(__FILE__)).'/../../../../upload_vidal/'.$this->cachefile;

        $this->arUrl = $masUrl;
        if (file_exists($this->cachefile) && time() - $this->cachetime < filemtime($this->cachefile)) {
            $this->getCache();
        }else{
            $this->loadXml();
            $this->setCache();
        }
    }

    /** Загружает XML из вне */
    public function loadXml(){
        $this->xml = simplexml_load_file($this->url);
    }

    /** Ищет препарат в $xml */
    public function findDrug($name){
        $elems = $this->xml->xpath("product[contains(concat(' ', name, ' '), ' $name ')]");
        $arr = array();
        $url = '';
        foreach ($elems as $elem){
            if ( isset($this->arUrl["$elem->code"]) ){
                $url =  $this->arUrl["$elem->code"] ;
                $arr[] = array(
                    'id' => $elem->code,
                    'manufacturer' => $elem->manufacturer,
                    'name' => $elem->name,
                    'price' => $elem->price,
                    'quantity' => $elem->quantity,
                    'url'   => $url,
                );
            }
        }
        return $arr;
    }

    /** Получить из кеша */
    public function getCache(){
        $this->xml = simplexml_load_file($this->cachefile);
        #$this->xml = new SimpleXMLElement($file);
    }

    /** Загрузить в кеш */
    public function setCache(){
        $this->xml->asXML($this->cachefile);
        #$cached = fopen($this->cachefile, 'w');
        #fwrite($cached, $xml);
        #fclose($cached);
    }

    public function replace_cyr ($path){
        $search = array ("'Ё'", "'А'", "'Б'", "'В'", "'Г'", "'Д'", "'Е'", "'Ж'", "'З'", "'И'", "'Й'", "'К'", "'Л'", "'М'", "'Н'", "'О'", "'П'", "'Р'", "'С'", "'Т'", "'У'", "'Ф'", "'Х'", "'Ц'", "'Ч'", "'Ш'", "'Щ'", "'Ъ'", "'Ы'", "'Ь'", "'Э'", "'Ю'", "'Я'");
        $replace = array ('ё', 'а', 'б', 'в', 'г', 'д', 'е', 'ж;', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $name = preg_replace ($search,$replace,$path);
        return $this->mb_ucfirst($name);
    }

    public function mb_ucfirst($str, $encoding='UTF-8')
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
            mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }

}

include 'piluliCodeUrl.php';


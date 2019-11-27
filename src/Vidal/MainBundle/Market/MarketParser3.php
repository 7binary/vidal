<?php

namespace Vidal\MainBundle\Market;

class MarketParser3{
    protected $url = 'http://www.zdravzona.ru/bitrix/catalog_export/yandex_b.php';
    protected $cachetime = 6000;
    protected $cachefile = 'cached3.xml';
    protected $xml;

    /** Подгружает XML */
    public function __construct(){

        $this->cachefile = dirname(dirname(__FILE__)).'/../../../../upload_vidal/'.$this->cachefile;

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
        $elems = $this->xml->xpath("shop/offers/offer[contains(concat(' ',model, ' '), ' $name ')]");
        $arr = array();
        foreach ($elems as $elem){
            $arr[] = array(
                'manufacturer' => $elem->vendor,
                'name' => $elem->model,
                'price' => $elem->price,
                'url' => $elem->url,
            );
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

$Market = new MarketParser3();
#$name = $Market->replace_cyr('АНАЛЬГИН');

#$name = strtolower($name);
#echo $name.'<br />';
$array = $Market->findDrug('Медела');
print_r($array);
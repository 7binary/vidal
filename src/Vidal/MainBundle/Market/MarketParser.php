<?php

namespace Vidal\MainBundle\Market;

class MarketParser{
    protected $url = 'http://vidal:3L29y4@ea.smacs.ru/exchange/price';
    protected $drugUrl = 'http://www.eapteka.ru/goods/drugs/otolaryngology/rhinitis/?id=';
    protected $cachetime = 6000;
    protected $cachefile = 'cached.xml';
    protected $xml;


    public function __construct(){

        $this->cachefile = dirname(dirname(__FILE__)).'/../../../../upload_vidal/'.$this->cachefile;

        if (file_exists($this->cachefile) && time() - $this->cachetime < filemtime($this->cachefile)) {
            $this->getCache();
        }else{
            $this->loadXml();
            $this->setCache();
        }
    }


    public function loadXml(){
        $this->xml = simplexml_load_file($this->url);
    }


    public function findDrug($name){
        $elems = $this->xml->xpath("product[contains(concat(' ', name, ' '), ' $name ')]");
        $arr = array();
        foreach ($elems as $elem){
            $arr[] = array(
                'id' => $elem->code,
                'manufacturer' => $elem->manufacturer,
                'name' => $elem->name,
                'price' => $elem->price,
                'quantity' => $elem->quantity,
                'url' => $this->drugUrl.$elem->code,
            );
        }
        return $arr;
    }


    public function getCache(){
        $this->xml = simplexml_load_file($this->cachefile);
        #$this->xml = new SimpleXMLElement($file);
    }


    public function setCache(){
        $this->xml->asXML($this->cachefile);
        #$cached = fopen($this->cachefile, 'w');
        #fwrite($cached, $xml);
        #fclose($cached);
    }

//    public function replace_cyr ($path){
//        $search = array ("'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'", "'?'");
//        $replace = array ('?', '?', '?', '?', '?', '?', '?', '?;', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?');
//        $name = preg_replace ($search,$replace,$path);
//        return $this->mb_ucfirst($name);
//    }

    public function mb_ucfirst($str, $encoding='UTF-8')
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
            mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }

}

<?php
namespace Vidal\MainBundle\Market;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Bundle\TwigBundle\TwigEngine as Templating;
use Symfony\Component\HttpFoundation\Session\Session;

class Basket{

    protected $drugs;

    public $session;

    public function __construct($request){
        $this->session = $request->getSession();
        if ( $this->session->get('basket')){
            $this->drugs = $this->session->get('basket');
        }else{
            $this->drugs = array();
        }
    }

    public function save(){
        $this->session->set('basket',$this->drugs);
    }

    public function get($key){
        if ( isset($this->drugs[$key]) ){
            return $this->drugs[$key];
        }else{
            return null;
        }
    }

    public function getProduct($code){
        if ( isset($this->drugs[$code]) ){
            return $this->drugs[$code];
        }else{
            return null;
        }
    }

    public function getAll(){
        $arr = array();
        foreach($this->drugs as $key => $drug ){
            $drug->setCode($key);
            $arr[$drug->getGroupApt()][]  = $drug;
//            $arr[$drug->getGroupApt()]['id'] = $key;
        }
        return $arr;
    }

    public function set($product){
        $this->drugs[$product->getCode()] = $product;
        $this->save();
    }

    public function add($product){
        if ( isset($this->drugs[$product->getCode()])){
            $this->drugs[$product->getCode()]->addCount($product->getCount());
        }else{
            $this->drugs[$product->getCode()] = $product;
        }
        $this->save();
    }

    public function remove($product){
        if (isset($this->drugs[$product->getCode()])){
            unset($this->drugs[$product->getCode()]);
            $this->save();
        }
    }

    public function removeAll(){
        $this->drugs = array();
        $this->save();
    }

    public function setProduct($product){
        $this->drugs[$product->getCode()] = $product;
        $this->save();
    }

    public function getAmounts(){
        $arr = array();
        foreach($this->drugs as $drug){
            if (isset($arr[$drug->getGroupApt()])){
                $arr[$drug->getGroupApt()]+=( $drug->getCount() * $drug->getPrice());
            }else{
                $arr[$drug->getGroupApt()]=( $drug->getCount() * $drug->getPrice());
            }
        }
        return $arr;
    }

    public function getSumma(){
        $arr = array();
        foreach($this->drugs as $drug){
            if (isset($arr[$drug->getGroupApt()])){
                $arr[$drug->getGroupApt()]+=( $drug->getCount() * $drug->getPrice());
            }else{
                $arr[$drug->getGroupApt()]=( $drug->getCount() * $drug->getPrice());
            }
        }
        return $arr;
    }

    public function getCount(){
        $count = 0;
        foreach($this->drugs as $drug){
            $count += $drug->getCount();
        }
        return $count;
    }

    public function clear($group){
        foreach($this->drugs as $key => $drug){
            if ($drug->getGroupApt() == $group){
                unset($this->drugs[$key]);
            }
        }
        $this->save();
    }
}

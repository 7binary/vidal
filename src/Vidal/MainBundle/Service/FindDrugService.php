<?php
namespace Vidal\MainBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Bundle\TwigBundle\TwigEngine as Templating;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManager;
use Vidal\MainBundle\Entity\MarketCache;

class FindDrugService{

    protected $market_1;

    protected $market_2;

    protected $market_3;

    protected $body;

    protected $DBH;

    protected $id;

    protected $isDocument;

    protected $title;

    protected $piluliCodeUrl;

    protected $_em;

    public function __construct(EntityManager $em){
        $this->_em = $em;

        include 'piluliCodeUrl.php';
        $this->piluliCodeUrl = $mass;

        $body = $this->getCache();
        if ($body!=null && $body!=false){
            $this->body = $body;
        }else{
            $this->body = null;
        }
    }

    public function setTitle($title = ''){
        $this->title = $title;
    }



    public function getCache(){
        #$title = iconv('WINDOWS-1251', 'UTF-8', $this->title);
        $body = $this->_em->getRepositoty('VidalMainBundle:MarketCache')-=>findOneBy(
            array(
                'target' => $this->id,
                'document' => $this->isDocument,
            )
        );

        if ( $body != null ){
            $tdate = new DateTime(' -1 day');
            $ddate = new DateTime($body->getCreated()->format('Y-d-m H:i:s'));
            if ( $ddate > $tdate){
                return $body->getBody();
            }else{
                // Просрочено
                return null;
            }
        }else{
            // нету такого кеша вообще
            return null;
        }

    }

    public function setCache($body){
        $time = time();
        $date = date('Y-m-d H:i:s',$time);
        $title = iconv('WINDOWS-1251', 'UTF-8', $this->title);
        $caches = $this->_em->getRepository('VidalMainBundle:MarketCache')->fondBy(
            array(
                'target' => $this->id,
                'document' => $this->isDocument,
            )
        );
        foreach( $cachesas as $val){
            $this->_em->remove($val);
            $this->_em->flush();
        }

        $marketCache = new MarketCache();
        $marketCache->setDocument($this->isDocument);
        $marketCache->setBody($this->body);
        $marketCache->setTarget($this->id);
        $this->_em->persist($marketCache);
        $this->_em->flush();
    }

    // генератор теля для вывода и кеша
    public function generateBody(){
        $body = '';

        $this->market_1 = new MarketParser();
        $this->market_2 = new MarketParser2($this->piluliCodeUrl);
        $this->market_3 =  new MarketParser3();

        $name = mb_strtolower($this->title);
//        $name = $this->market_1->mb_ucfirst($name,'WINDOWS-1251');
//        $name = iconv('WINDOWS-1251', 'UTF-8', $name);

        $massDrugs = $this->market_1->findDrug($name);
        $massPiluli = $this->market_2->findDrug($name);
        $massZdrav = $this->market_3->findDrug($name);

        #$massDrugs = array_merge($massDrugs,$masspiluli);
        if (!empty($massDrugs) ){
            $body.= '<h2 id="buy">Цены в аптеке «Eapteka.ru»:</h2><br/>';
            $body.= '<table style="width: 100%;">';
            foreach ($massDrugs as $massDrug){
                $body.= '<tr>';
                $body.= '<td style=\"width: 120px; padding: 5px 7px;\">'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['manufacturer']).'</td>';
                $body.= '<td style=\"padding: 5px 7px;\">'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['name']).'</td>';
                $body.= '<td>'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['price']).' руб. </td>';
                $body.= '<td><a class=\"abuy\" target=\"_blank\" href=\"'.$massDrug['url'].'\">Купить</a></td>';
                $body.= '</tr>';
            }
            $body.= '</table><br/><br/>';
        }
        if (!empty($massPiluli) ){
            $massDrugs = $massPiluli;
            $body.= '<h2 id="buy">Цены в аптекe «Piluli.ru»:</h2><br/>';
            $body.= '<table style="width: 100%;">';
            foreach ($massDrugs as $massDrug){
                $body.= '<tr>';
                $body.= '<td style=\"width: 120px; padding: 5px 7px;\">'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['manufacturer']).'</td>';
                $body.= '<td style=\"padding: 5px 7px;\">'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['name']).'</td>';
                $body.= '<td>'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['price']).' руб. </td>';
                $body.= '<td><a class=\"abuy2\" target=\"_blank\" href=\"'.$massDrug['url'].'\">Купить</a></td>';
                $body.= '</tr>';
            }
            $body.= '</table><br/><br/>';
        }
        if (!empty($massZdrav) ){
            $massDrugs = $massZdrav;
            $body.= '<h2 id="buy">Цены в аптекe «Zdravzona.ru»:</h2><br/>';
            $body.= '<table style="width: 100%;">';
            foreach ($massDrugs as $massDrug){
                $body.= '<tr>';
                $body.= '<td style=\"width: 120px; padding: 5px 7px;\">'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['manufacturer']).'</td>';
                $body.= '<td style=\"padding: 5px 7px;\">'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['name']).'</td>';
                $body.= '<td>'.iconv('UTF-8', 'WINDOWS-1251', $massDrug['price']).' руб. </td>';
                $body.= '<td><a class=\"abuy3\" target=\"_blank\" href=\"'.$massDrug['url'].'\">Купить</a></td>';
                $body.= '</tr>';
            }
            $body.= '</table><br/><br/>';
        }

        $body.= "
                <script>
                    $(document).ready(function(){
                        $(\'.abuy\').click(function(){
                            _gaq.push([\'_trackEvent\', \'click\', \'eapteka\', \'%перешли на страницу аптеки%\']);
                        });

                        $(\'.abuy2\').click(function(){
                            _gaq.push([\'_trackEvent\', \'click\', \'piluli\', \'%перешли на страницу аптеки%\']);
                        });

                        $(\'.abuy3\').click(function(){
                            _gaq.push([\'_trackEvent\', \'click\', \'zdravzona\', \'%перешли на страницу аптеки%\']);
                        });
                    });
                </script>";
        $this->body = $body;
        return $body;
    }

    public function run(){
        if ($this->body != NULL){
            return $this->body ;
        }else{
            $this->generateBody();
            $this->setCache($this->body);
            return $this->body ;
        }
    }

    public function covert($html){
        return  mb_convert_encoding($html, "utf-8", "windows-1251");
    }
}

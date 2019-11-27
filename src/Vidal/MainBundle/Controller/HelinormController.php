<?php
namespace Vidal\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class HelinormController extends Controller
{

	/**
	 * @Route("/helinorm" ,name="helinorm")
	 * @Template("VidalMainBundle:Helinorm:index.html.twig")
	 */
	public function indexAction()
	{
		return array(
			'title'       => 'Helinorm',
			'description' => 'Helinorm',
			'keywords'    => 'Helinorm',
			'noYad'       => true,
			'menu_left'   => 'helinorm',
		);
	}
	
	
	/**
	 * @Route("/helinorm2" ,name="helinorm2")
	 * @Template("VidalMainBundle:Helinorm:index2.html.twig")
	 */
	public function index2Action()
	{
		return array(
			'title'       => 'Helinorm',
			'description' => 'Helinorm',
			'keywords'    => 'Helinorm',
			'noYad'       => true,
			'menu_left'   => 'helinorm',
		);
	}

    /**
     * @Route("/helinorm-test/{number}" ,name="helinorm_test", options={"expose" = true})
     * @Template("VidalMainBundle:Helinorm:test.html.twig")
     */
    public function testAction(Request $request, $number){
        $test = array(
            0 => array(
                'question' => '<b>ВОПРОС №1 Из 6:</b>&nbsp;&nbsp;&nbsp;&nbsp;Были/есть ли у Ваших родственников серьезные случаи заболевания желудка (рак, язвенная болезнь)?',
                'answers' => array(
                    0 => 'Нет',
                    1 => 'Были/есть, но не такие серьезные',
                    2 => 'Были/есть',
                ),
            ),
            1 => array(
                'question' => '<b>ВОПРОС №2 Из 6:</b>&nbsp;&nbsp;&nbsp;&nbsp;Как часто у Вас бывает изжога?',
                'answers' => array(
                    0 => 'Редко или никогда',
                    1 => 'Случается периодически как реакция на прием тяжелой или острой пищи',
                    2 => 'Мучаюсь каждый день',
                ),
            ),
            2 => array(
                'question' => '<b>ВОПРОС №3 Из 6:</b>&nbsp;&nbsp;&nbsp;&nbsp;Часто ли у Вас бывает отрыжка после приема пищи?',
                'answers' => array(
                    0 => 'Не помню такого',
                    1 => 'Бывает, в зависимости от блюда, которое съел',
                    2 => 'Каждый раз после еды',
                ),
            ),
            3 => array(
                'question' => '<b>ВОПРОС №4 Из 6:</b>&nbsp;&nbsp;&nbsp;&nbsp;Испытываете ли Вы боли и дискомфорт в желудке, которые проходят или, напротив, усиливаются после приема пищи?',
                'answers' => array(
                    0 => 'Никогда такого не было',
                    1 => 'Очень редко, но есть пища, на которую мой желудок так реагирует',
                    2 => 'Практически после каждого приема пищи',
                ),
            ),
            4 => array(
                'question' => '<b>ВОПРОС №5 Из 6:</b>&nbsp;&nbsp;&nbsp;&nbsp;Не замечали ли Вы повышенного газоотделения (вздутия живота) после приема пищи?',
                'answers' => array(
                    0 => 'Только если выпью много газированной воды',
                    1 => 'Изредка, после приема острой или жирной пищи, после употребления алкоголя',
                    2 => 'Мучаюсь с этой проблемой постоянно',
                ),
            ),
            5 => array(
                'question' => '<b>ВОПРОС №6 Из 6:</b>&nbsp;&nbsp;&nbsp;&nbsp;Как часто Вы принимаете обезболивающие средства (например, аспирин, диклофенак, ибупрофен)?',
                'answers' => array(
                    0 => 'Никогда',
                    1 => 'Иногда',
                    2 => 'Часто',
                ),
            ),
        );

        $testResult = array(
          0 => array(
              'title' => 'Результат: вам не о чем беспокоиться, но забывать о профилактике не стоит!',
              'text' => 'Скорее всего, у Вас не наблюдается каких-либо видимых проблем с желудочно-кишечным трактом. <a href="http://ad.adriver.ru/cgi-bin/click.cgi?sid=1&bt=2&ad=511031&pid=1919501&bid=3782870&bn=3782870&rnd=509224482" target="_blank">Правильный режим питания</a> и здоровый образ жизни, плановые профилактические визиты к врачу – залог здоровья в будущем.',
              ),
          1 => array(
              'title' => 'Результат: состояние Вашего желудочно-кишечного тракта не идеально',
              'text' => 'Проявите повышенную заботу о своем желудке. Возможно, Вам следует пересмотреть образ жизни и привычки питания. Желательно обратиться к врачу-гастроэнтерологу и пройти <a href="http://ad.adriver.ru/cgi-bin/click.cgi?sid=1&bt=2&ad=511031&pid=1919501&bid=3782871&bn=3782871&rnd=168803413" target="_blank">профилактическое обследование</a>.',
          ),
          2 => array(
              'title' => 'Результат: Вам необходимо срочно обратиться к врачу.',
              'text' => 'Срочно займитесь своим здоровьем. Обязательно посетите врача-гастроэнтеролога и пройдите обследование желудочно-кишечного тракта, сдайте тест на наличие <a href="http://ad.adriver.ru/cgi-bin/click.cgi?sid=1&bt=2&ad=511031&pid=1919501&bid=3782872&bn=3782872&rnd=1778476428" target="_blank">Хеликобактер пилори</a> и готовьтесь к тому, что образ жизни и режим питания придется менять.',
          ),
        );


        if ( $request->getMethod() == 'POST'){
            $session = $request->getSession();
            $result = $session->get('heliresult');
            if ($result == null) $result = 0;
            $result += $request->request->get('answerNum');
            $session->set('heliresult', $result);
        }else{
            $session = $request->getSession();
            $result = $session->set('heliresult', null);
        }
        if ($number > 5){
            if ($result <=4 ){
                $variable = 0;
            }elseif($result <=9){
                $variable = 1;
            }else{
                $variable = 2;
            }
            return array('result' => $testResult[$variable],'number' => $number+1);
        }else{
            return array('question' => $test[$number],'number' => $number+1);
        }
    }

    /**
     * @Route("/helinorm-page/{number}" ,name="helinorm_page", options={"expose" = true})
     * @Template("VidalMainBundle:Helinorm:page.html.twig")
     */
    public function pageAction(Request $request, $number){
        return array('number' => $number);
    }
}

<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Lsw\SecureControllerBundle\Annotation\Secure;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Vidal\MainBundle\Entity\City;
use Vidal\MainBundle\Entity\DeliveryLog;
use Vidal\MainBundle\Entity\Specialty;
use Vidal\MainBundle\Entity\UploadUsers;
use Vidal\MainBundle\Entity\User;
use Vidal\MainBundle\Form\Type\UploadUsersType;

/**
 * Class UploadUsersController
 *
 * @package Vidal\MainBundle\Controller
 * @Secure(roles="ROLE_SUPERADMIN")
 */
class UploadUsersController extends Controller
{
    /** @var EntityManager */
    protected $em;

    /** @var UploadUsers */
    protected $model;

    /**
     * @Route("/upload-users/remove/{id}", name="upload_users_remove")
     */
    public function uploadUsersRemoveActions(Request $request, $id) {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();
        $upload = $em->getRepository('VidalMainBundle:UploadUsers')->findOneById($id);

        $em->remove($upload);
        $em->flush();

        $this->get('session')->getFlashBag()->add('msg', 'Запись удалена');

        return $this->redirect($this->generateUrl('upload_users_file'));
    }

    /**
     * @Route("/upload-users/file", name="upload_users_file")
     * @Template("VidalMainBundle:UploadUsers:file.html.twig")
     */
    public function uploadUsersFileActions(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getEntityManager();
        $model = new UploadUsers();
        $uploads = $em->getRepository('VidalMainBundle:UploadUsers')->findAll();

        $form = $this->createForm(new UploadUsersType(), $model);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($model);
            $em->flush();
            $em->refresh($model);

            $file = $model->getFile();
            $filename = $this->container->getParameter('upload_dir') . '/users' . $file['fileName'];

            # формируем читаемый ассоциативный массив читаем все в него и закрываем файл
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject($filename);
            $data = null;
            foreach ($phpExcelObject->getWorksheetIterator() as $worksheet) {
                $data = $worksheet->toArray();
                break;
            }
            unset($phpExcelObject);

            # формируем читаемый ассоциативный массив
            if ($model->getSkipFirstLine()) {
                array_shift($data);
            }

            $fields = $model->getFieldsSplitted();
            $rows = array();

            foreach ($data as $row) {
                $rowData = array_combine($fields, $row);
                if (empty($rowData[UploadUsers::FIELD_EMAIL]) || empty($rowData[UploadUsers::FIELD_FIO])) {
                    continue;
                }
                $rows[] = $rowData;
            }

            if ($rows == 0) {
                $form->get('file')->addError(new FormError('Файл в неверном формате. Ни одной строки не загружено.'));
                $em->remove($model);
                $em->flush($model);
            }

            # если предпросмотр, то отображаем первую ячейку и удаляем запись
            if ($model->getPreview()) {
                header('Content-Type:text/html; charset=utf-8');
                echo "<pre style='margin-bottom:30px;'>";
                print_r($rows[0]);
                echo "</pre>";
                echo "<b>Это вывод первой записи на проверку корректности полей. Для выхода из режима предпросмотра нажмите кнопку НАЗАД браузера и снимите галочку</b>";
                $em->remove($model);
                $em->flush();
                exit;
            }

            $this->em = $em;
            $this->model = $model;

            # перед проведением всех операций проверка правильности полей
            for ($i = 0; $i < count($rows); $i++) {
                $row = $rows[$i];

                if ($error = $this->checkRow($row)) {
                    $errorMsg = 'Ошибка импорта строки #' . ($i + 1) . ': '
                        . json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    $form->get('file')->addError(new FormError($errorMsg));
                    $form->get('file')->addError(new FormError($error));
                    $em->remove($model);
                    $em->flush($model);

                    return array(
                        'form' => $form->createView(),
                        'title' => 'Загрузка участников',
                        'uploads' => $uploads,
                    );
                }
            }

            $model->updateDeliveryId();
            $model->setRawEncode($rows);
            $model->setStatus(UploadUsers::STATUS_NEW);
            $em->flush($model);

            return $this->redirect($this->generateUrl('upload_users_file'), 301);
        }

        return array(
            'form' => $form->createView(),
            'title' => 'Загрузка участников',
            'uploads' => $uploads,
        );
    }

    private function checkRow(array $row)
    {
        $fio = trim($row[UploadUsers::FIELD_FIO]);
        $email = trim($row[UploadUsers::FIELD_EMAIL]);

        if (empty($email)) {
            return 'Не указан EMAIL';
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'EMAIL указан в неверном формате';
        }
        elseif (empty($fio)) {
            return 'Не указан ФИО';
        }

        $this->model->addTotal();
        $user = $this->em->getRepository('VidalMainBundle:User')->findOneByUsername($email);
        $user ? $this->model->addOld() : $this->model->addNew();

        return null;
    }
}
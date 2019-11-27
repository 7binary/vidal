<?php

namespace Vidal\MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Vidal\MainBundle\Entity\KeyValue;
use Vidal\MainBundle\Entity\User;
use Vidal\MainBundle\Entity\UserDevice;
use Vidal\MainBundle\Form\Type\ProfileType;
use Vidal\MainBundle\Form\Type\RegisterType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lsw\SecureControllerBundle\Annotation\Secure;

class ApiController extends Controller
{
    const TAG_NEURO = 'neuro';
    const TAG_CARDIO = 'cardio';
    const TAG_ENDOCRINO = 'endocrino';
    const TAG_ANDROID = 'android';
    const TAG_GYNECO = 'gyneco';
    const TAG_VETERINARY = 'veterinary';

    /**
     * @Route("/api/user/auth" ,name="api_user_auth")
     */
    public function userAuthAction(Request $request)
    {
        $success = false;
        $username = $request->request->get('username', null);
        $password = $request->request->get('password', null);

        if (!empty($password)) {
            $password = strrev(base64_decode($password));
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository('VidalMainBundle:User')->findOneByLogin($username);
        $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY);

        if (empty($user)) {
            return new JsonResponse(array('username' => 'Неверный логин или пароль'), 400);
        }

        if ($user->getEmailConfirmed() == false) {
            return new JsonResponse(array('username' => 'Пожалуйста, перейдите по ссылке из отправленного на адрес Вашей электронной почты письма для завершения процедуры регистрации'), 401);
        }

        if ($user) {
            # pwReal - это уже захешированный в MD5 пароль
            $pwReal = $user->getPassword();

            # пользователей со старой БД проверям с помощью mysql-функций
            if (md5($password) === $pwReal) {
                $success = true;
            }
            elseif ($user->getOldUser()) {
                $success = $em->getRepository('VidalMainBundle:User')->checkOldPassword($password, $pwReal);
            }
        }

        if ($success) {
            return new JsonResponse(array(
                'status' => 'success',
                'token' => $keyValue->getValue(),
                'username' => $username,
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'city' => $user->getCity() ? $user->getCity()->getTitle() : '',
                'primarySpecialty' => $user->getPrimarySpecialty() ? $user->getPrimarySpecialty()->getTitle() : '',
                'birthdate' => $user->getBirthdate() ? $user->getBirthdate()->format('d.m.Y') : '',
                'user_id' => $user->getId(),
            ), 201);
        }
        else {
            return new JsonResponse(array('username' => 'Неверный логин или пароль'), 400);
        }
    }

    /**
     * @Route("/api/user/add" ,name="api_user_add")
     */
    public function userAddAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $form = $this->createForm(new RegisterType($em, true), $user);
        $errors = array();

        $formData = $request->request->get('register');

        if (!empty($formData) && !empty($formData['password'])) {
            $password = strrev(base64_decode($formData['password']));
            $formData['password'] = $password;
            $request->request->set('register', $formData);
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $oldUser = $em->getRepository('VidalMainBundle:User')->findByUsername($user->getUsername());

            if (empty($oldUser)) {
                # e-mail свободен, сохраняем пользователя
                $user->refreshHash();
                $user->hashPassword();
                $em->persist($user);
                $em->flush();
                $em->refresh($user);

                # уведомление пользователя о регистрации
                $this->get('email.service')->send(
                    $user->getUsername(),
                    array('VidalMainBundle:Email:registration.html.twig', array('user' => $user)),
                    'Благодарим за регистрацию на нашем портале!'
                );

                return new JsonResponse(array(
                    'status' => 'success',
                    'user_id' => $user->getId(),
                ), 201);
            }

            $errors = array('username' => 'E-mail уже занят. Пожалуйста, укажите другой. Если Вы уже использовали ранее данный e-mail для регистрации на vidal.ru, просто авторизуйтесь в приложении.');
        }

        foreach ($form->getIterator() as $key => $child) {
            if ($child instanceof Form) {
                foreach ($child->getErrors() as $error) {
                    $errors[$key] = $error->getMessage();
                }
            }
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * @Route("/api/user/set-ios-id" ,name="api_user_set_ios_id")
     */
    public function userSetIosIdAction(Request $request)
    {
        try {
            if ($this->checkToken() == false) {
                return new JsonResponse(array(
                    'token' => 'Неверный токен',
                ), 400);
            }

            $username = $request->request->get('username', null);
            $androidId = $request->request->get('id', null);
            $gcm = $request->request->get('gcm', null);
            $project = $request->request->get('project', 'cardio');

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            if (empty($androidId)) {
                return new JsonResponse(array('id' => "Должен быть указан 'id' - идентификатор девайса"), 400);
            }

            if (empty($gcm)) {
                return new JsonResponse(array('gcm' => "Должен быть указан 'gcm' - ключ для API Google Cloud Messaging"), 400);
            }

            /** @var UserDevice $userDevice */
            if ($userDevice = $em->getRepository('VidalMainBundle:User')->findDeviceByAndroidId($androidId)) {
                $userDevice->setGcm($gcm);
                $userDevice->setProject($project);
                $userDevice->setIos(true);
                $em->flush($userDevice);
            }
            else {
                $userDevice = new UserDevice();

                if (!empty($username)) {
                    if ($user = $em->getRepository('VidalMainBundle:User')->findOneByLogin($username)) {
                        $userDevice->setUser($user);
                    }
                }

                $userDevice->setAndroidId($androidId);
                $userDevice->setGcm($gcm);
                $userDevice->setProject($project);
                $userDevice->setIos(true);

                $em->persist($userDevice);
                $em->flush();
            }

            return new JsonResponse(array(
                'status' => 'success',
            ), 200);
        }
        catch (\Exception $e) {
            return new JsonResponse(array(
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ), 400);
        }
    }

    /**
     * @Route("/api/user/set-android-id" ,name="api_user_set_android_id")
     */
    public function userSetAndroidIdAction(Request $request)
    {
        try {
            if ($this->checkToken() == false) {
                return new JsonResponse(array(
                    'token' => 'Неверный токен',
                ), 400);
            }

            $username = $request->request->get('username', null);
            $androidId = $request->request->get('id', null);
            $gcm = $request->request->get('gcm', null);
            $project = $request->request->get('project', 'cardio');

            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            if (empty($androidId)) {
                return new JsonResponse(array('id' => "Должен быть указан 'id' - идентификатор девайса"), 400);
            }

            if (empty($gcm)) {
                return new JsonResponse(array('gcm' => "Должен быть указан 'gcm' - ключ для API Google Cloud Messaging"), 400);
            }

            /** @var UserDevice $userDevice */
            if ($userDevice = $em->getRepository('VidalMainBundle:User')->findDeviceByAndroidId($androidId)) {
                $userDevice->setGcm($gcm);
                $userDevice->setProject($project);
                $em->flush($userDevice);
            }
            else {
                $userDevice = new UserDevice();

                if (!empty($username)) {
                    if ($user = $em->getRepository('VidalMainBundle:User')->findOneByLogin($username)) {
                        $userDevice->setUser($user);
                    }
                }

                $userDevice->setAndroidId($androidId);
                $userDevice->setGcm($gcm);
                $userDevice->setProject($project);

                $em->persist($userDevice);
                $em->flush();
            }

            return new JsonResponse(array(
                'status' => 'success',
            ), 200);
        }
        catch (\Exception $e) {
            return new JsonResponse(array(
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ), 400);
        }
    }

    /**
     * @Route("/api/user/unset-android-id" ,name="api_user_unset_android_id")
     */
    public function userUnsetAndroidIdAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $androidId = $request->request->get('id', null);

        if (empty($androidId)) {
            return new JsonResponse(array('id' => "Должен быть указан 'id' - идентификатор девайса"), 400);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->createQuery("DELETE FROM VidalMainBundle:UserDevice u WHERE u.androidId = '$androidId'")->execute();

        return new JsonResponse(array(
            'status' => 'success',
        ), 200);
    }

    /**
     * @Route("/api/user/edit-new" ,name="api_user_edit_new")
     */
    public function userEditNewAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $errors = array();

        $username = $request->request->get('username', null);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var User $profile */
        $profile = $em->getRepository('VidalMainBundle:User')->findOneByLogin($username);

        if ($profile == null) {
            return new JsonResponse(array('username' => "Профиль участника $username не найден"), 400);
        }

        $form = $request->get('profile');

        if (isset($form['firstName'])) {
            $profile->setFirstName($form['firstName']);
        }

        if (isset($form['lastName'])) {
            $profile->setLastName($form['lastName']);
        }

        if (isset($form['surName'])) {
            $profile->setSurName($form['surName']);
        }

        if (isset($form['birthday'])) {
            if (empty($form['birthday'])) {
                $errors[] = 'Необходимо указать дату рождения';
            }
            elseif (!isset($form['birthday']['day'])
                || !isset($form['birthday']['month'])
                || !isset($form['birthday']['year'])
            ) {
                $errors[] = 'Дата рождения указана в неверном формате';
            }
            else {
                $date = $form['birthday']['day'] . '.' . $form['birthday']['month'] . '.' . $form['birthday']['year'];
                $date = new \DateTime($date);
                $profile->setBirthdate($date);
            }
        }

        if (isset($form['city'])) {
            if (empty($form['city'])) {
                $errors['city'] = 'Необходимо указать город';
            }
            elseif ($city = $this->findCity($form['city'])) {
                if ($city->getTitle() != '') {
                    $profile->setCity($city);
                }
            }
            else {
                $errors['city'] = 'Необходимо указано значение города';
            }
        }

        if (isset($form['educationType'])) {
            if (empty($form['educationType'])) {
                $profile->setEducationType(null);
            }
            else {
                $profile->setEducationType($form['educationType']);
            }
        }

        if (isset($form['academicDegree'])) {
            if (empty($form['academicDegree'])) {
                $profile->setAcademicDegree(null);
            }
            else {
                $academicDegrees = User::getAcademicDegrees();
                $key = $form['academicDegree'];

                if (isset($academicDegrees[$key])) {
                    $profile->setAcademicDegree($academicDegrees[$key]);
                }
                else {
                    $errors['academicDegree'] = 'Недопустимое значение Ученой степени';
                }
            }
        }

        if (isset($form['university'])) {
            if (empty($form['university'])) {
                $profile->setUniversity(null);
            }
            elseif ($university = $em->getRepository('VidalMainBundle:University')->findOneById(intval($form['university']))) {
                $profile->setUniversity($university);
            }
            else {
                $errors['university'] = 'Недопустимое значение учебного заведения';
            }
        }

        if (isset($form['graduateYear'])) {
            if (empty($form['graduateYear'])) {
                $profile->setGraduateYear(null);
            }
            else {
                if (intval($form['graduateYear']) < 1900 || intval($form['graduateYear']) > 2030) {
                    $errors['graduateYear'] = 'Недопустимое значение года выпуска';
                }
                else {
                    $date = '01.01.' . $form['graduateYear'];
                    $date = new \DateTime($date);
                    $profile->setGraduateYear($date);
                }
            }
        }

        if (isset($form['primarySpecialty'])) {
            if (empty($form['primarySpecialty'])) {
                $errors['primarySpecialty'] = 'Необходимо заполнить первичную специальность';
            }
            elseif ($ps = $em->getRepository('VidalMainBundle:Specialty')->findOneById(intval($form['primarySpecialty']))) {
                $profile->setPrimarySpecialty($ps);
            }
            else {
                $errors['primarySpecialty'] = 'Недопустимое значение первичной специальности';
            }
        }

        if (isset($form['secondarySpecialty'])) {
            if (empty($form['secondarySpecialty'])) {
                $profile->setSecondarySpecialty(null);
            }
            elseif ($ss = $em->getRepository('VidalMainBundle:Specialty')->findOneById(intval($form['secondarySpecialty']))) {
                $profile->setSecondarySpecialty($ss);
            }
            else {
                $errors['secondarySpecialty'] = 'Недопустимое значение вторичной специальности';
            }
        }

        if (!empty($errors)) {
            return new JsonResponse($errors, 400);
        }

        $em->flush($profile);

        return new JsonResponse(array('status' => 'success'), 200);
    }

    private function findCity($string)
    {
        if (empty($string)) {
            return null;
        }

        $titles = explode(',', $string);
        $city = trim($titles[0]);
        $region = null;
        $country = null;

        if (isset($titles[2])) {
            $region = trim($titles[1]);
            $country = trim($titles[2]);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $builder = $em->createQueryBuilder();

        $builder
            ->select('city')
            ->from('VidalMainBundle:City', 'city')
            ->leftJoin('VidalMainBundle:Country', 'country', 'WITH', 'country = city.country')
            ->where('city.title = :city')
            ->orderBy('country.id', 'ASC')
            ->setParameter('city', $city)
            ->setMaxResults(1);

        if ($country) {
            $builder
                ->leftJoin('city.country', 'c')
                ->leftJoin('city.region', 'r')
                ->andWhere('c.title LIKE :country')
                ->andWhere('r.title LIKE :region')
                ->setParameter('country', $country)
                ->setParameter('region', $region);
        }

        $city = $builder->getQuery()->getOneOrNullResult();

        return $city;
    }

    /**
     * @Route("/api/user/edit" ,name="api_user_edit")
     */
    public function userEditAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $username = $request->request->get('username', null);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('VidalMainBundle:User')->findOneByLogin($username);

        if ($profile == null) {
            return new JsonResponse(array('username' => "Профиль участника $username не найден"), 400);
        }

        $form = $this->createForm(new ProfileType($em, true), $profile);
        $errors = array();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($profile);
            $em->flush();

            return new JsonResponse(array('status' => 'success'), 200);
        }

        foreach ($form->getIterator() as $key => $child) {
            if ($child instanceof Form) {
                foreach ($child->getErrors() as $error) {
                    $errors[$key] = $error->getMessage();
                }
            }
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * @Route("/api/universities" ,name="api_universities")
     */
    public function universitiesAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $data = $em->createQuery('SELECT u FROM VidalMainBundle:University u')->getArrayResult();

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/specialties" ,name="api_specialties")
     */
    public function specialtiesAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $data = $em->createQuery('SELECT s FROM VidalMainBundle:Specialty s')->getArrayResult();

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/news/{id}" ,name="api_news_item")
     */
    public function newsItemAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $publication = $em->getRepository('VidalDrugBundle:Publication')->findForApiById($id);

        if(isset($publication['bodyMobile']) && $publication['bodyMobile']) {
            $publication['body'] = $publication['bodyMobile'];
        }
        unset($publication['bodyMobile']);
        return new JsonResponse($publication, 200);
    }

    /**
     * @Route("/api/news" ,name="api_news")
     */
    public function newsListAction(Request $request)
    {
        $from = $request->query->get('from', 0);
        $size = $request->query->get('size', 10);
        $project = $request->query->get('project', null);

        if (empty($project)) {
            $project = $request->request->get('project', null);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $news = $em->getRepository('VidalDrugBundle:Publication')->findForApi($from, $size, $project);

        return new JsonResponse($news, 200);
    }

    /**
     * @Route("/api/news-raw/{id}" ,name="api_news_raw_item")
     */
    public function newsRawItemAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager('drug');
        $publication = $em->getRepository('VidalDrugBundle:Publication')->findRawForApiById($id);

        return new JsonResponse($publication, 200);
    }

    /**
     * @Route("/api/news-raw" ,name="api_news_raw")
     */
    public function newsRawListAction(Request $request)
    {
        $from = $request->query->get('from', 0);
        $size = $request->query->get('size', 10);
        $project = $request->query->get('project', null);

        if (empty($project)) {
            $project = $request->request->get('project', null);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager('drug');
        $news = $em->getRepository('VidalDrugBundle:Publication')->findRawForApi($from, $size, $project);

        return new JsonResponse($news, 200);
    }

    /**
     * @Route("/api/download-db" ,name="api_download_db")
     */
    public function downloadDbAction()
    {
        $tags = self::getTags();
        $tag = $this->getRequest()->request->get('tag', null);

        if (!in_array($tag, $tags)) {
            return new JsonResponse(array(
                'tag' => 'Неверный код проекта',
            ), 400);
        }

        $filename = self::getFilenameByTag($tag);
        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $filename;

        header('Content-Type: application/zip, application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        exit;
    }

    /**
     * @Route("/archive/{name}", name="api_archive", requirements={"url"=".+"})
     */
    public function archiveAction($name)
    {
        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $name;

        header('Content-Type: application/zip, application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        exit;
    }

    /**
     * @Route("/archive-android/{name}", name="api_archive_android", requirements={"url"=".+"})
     */
    public function archiveAndroidAction($name)
    {
        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . 'cardio_android.zip';

        header('Content-Type: application/zip, application/octet-stream');
        header('Content-Disposition: attachment; filename="cardio_android.zip"');
        exit;
    }

    /**
     * @Route("/archive-neuro/{name}", name="api_archive_neuro", requirements={"url"=".+"})
     */
    public function archiveNeuroAction($name)
    {
        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . 'neuro.zip';

        header('Content-Type: application/zip, application/octet-stream');
        header('Content-Disposition: attachment; filename="neuro.zip"');
        exit;
    }

    /**
     * @Route("/archive-endocrino/{name}", name="api_archive_endocrino", requirements={"url"=".+"})
     */
    public function archiveEndocrinoAction($name)
    {
        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . 'endocrino.zip';

        header('Content-Type: application/zip, application/octet-stream');
        header('Content-Disposition: attachment; filename="endocrino.zip"');
        exit;
    }

    public static function getTags()
    {
        return array(
            self::TAG_NEURO,
            self::TAG_CARDIO,
            self::TAG_ENDOCRINO,
            self::TAG_ANDROID,
            self::TAG_GYNECO,
            self::TAG_VETERINARY,
        );
    }

    public static function getFilenameByTag($tag)
    {
        switch ($tag) {
            case self::TAG_CARDIO:
                $filename = 'encrypt.vidal.cardio';
                break;
            case self::TAG_ANDROID:
                $filename = 'cardio_android';
                break;
            default:
                $filename = $tag;
        }

        return $filename . '.zip';
    }

    /**
     * @Route("/api/db/update", name="api_db_update")
     */
    public function dbUpdateAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $tags = self::getTags();
        $tag = $this->getRequest()->request->get('tag', null);

        if (!in_array($tag, $tags)) {
            return new JsonResponse(array(
                'tag' => 'Неверный код проекта',
            ), 400);
        }

        $debug = $this->getRequest()->request->get('debug', null);
        $dir = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR;
        $filename = self::getFilenameByTag($tag);

        if ($debug) {
            $filename = 'debug_' . $filename;
        }

        $file = $dir . $filename;

        if (file_exists($file) == false) {
            return new JsonResponse(array(
                'file' => 'Не найден файл: ' . $filename,
            ), 400);
        }

        $modified = filemtime($file);

        return new JsonResponse(array(
            'url' => 'https://www.vidal.ru/archive/' . $filename,
            'version' => $modified,
        ), 201);
    }

    /**
     * @Route("/api/db/update-android", name="api_db_update_android")
     */
    public function dbUpdateAndroidAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . 'cardio_android.zip';

        $modified = filemtime($file);

        return new JsonResponse(array(
            'url' => 'https://www.vidal.ru/archive/cardio_android.zip',
            'version' => $modified,
        ), 201);
    }

    /**
     * @Route("/api/db/update-neuro", name="api_db_update_neuro")
     */
    public function dbUpdateNeuroAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . 'neuro.zip';

        $modified = filemtime($file);

        return new JsonResponse(array(
            'url' => 'https://www.vidal.ru/archive/neuro.zip',
            'version' => $modified,
        ), 201);
    }

    /**
     * @Route("/api/db/update-endocrino", name="api_db_update_neuro")
     */
    public function dbUpdateEndocrinoAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR
            . 'endocrino.zip';

        $modified = filemtime($file);

        return new JsonResponse(array(
            'url' => 'https://www.vidal.ru/archive/endocrino.zip',
            'version' => $modified,
        ), 201);
    }

    /**
     * @Route("/api/db/auth", name="api_db_auth")
     */
    public function dbAuthAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $tags = self::getTags();
        $tag = $this->getRequest()->request->get('tag', null);

        if (!in_array($tag, $tags)) {
            return new JsonResponse(array(
                'tag' => 'Неверный код проекта',
            ), 400);
        }

        /** @var EntityManager $em */
        /** @var KeyValue $keyValue */
        $em = $this->getDoctrine()->getManager();

        switch ($tag) {
            case self::TAG_NEURO:
                $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_NEURO);
                break;
            case self::TAG_ENDOCRINO:
                $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_ENDOCRINO);
                break;
            case self::TAG_GYNECO:
                $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_GYNECO);
                break;
            case self::TAG_VETERINARY:
                $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_VETERINARY);
                break;
            default:
                $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_PART);
        }

        return new JsonResponse(array(
            'key' => $keyValue->getValue(),
        ), 201);
    }

    /**
     * @Route("/api/db/auth-neuro", name="api_db_auth_neuro")
     */
    public function dbAuthNeuroAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var KeyValue $keyValue */
        $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_NEURO);

        return new JsonResponse(array(
            'key' => $keyValue->getValue(),
        ), 201);
    }

    /**
     * @Route("/api/devices", name="api_devices")
     */
    public function devicesAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $devices = $em->getRepository('VidalMainBundle:User')->findDevices();

        return new JsonResponse($devices, 201);
    }

    private function checkToken()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $keyValue = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY);

        return $keyValue->getValue() == $this->getRequest()->request->get('token');
    }

    /**
     * @Route("/api/profile", name="api_profile")
     */
    public function profileAction(Request $request)
    {
        if ($this->checkToken() == false) {
            return new JsonResponse(array(
                'token' => 'Неверный токен',
            ), 400);
        }

        $username = $request->request->get('username', null);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $profile = $em->getRepository('VidalMainBundle:User')->findProfile($username);

        if ($profile == null) {
            return new JsonResponse(array('username' => "Профиль участника $username не найден"), 400);
        }

        if ($profile['graduateYear'] instanceof \DateTime) {
            $profile['graduateYear'] = $profile['graduateYear']->format('Y');
        }
        if ($profile['birthdate'] instanceof \DateTime) {
            $profile['birthdate'] = $profile['birthdate']->format('d.m.Y');
        }
        if ($profile['created'] instanceof \DateTime) {
            $profile['created'] = $profile['created']->format('d.m.Y');
        }

        return new JsonResponse($profile, 200);
    }

    /**
     * @Route("/upload_cardio", name="upload_cardio")
     *
     * @Template("VidalMainBundle:Api:upload_cardio.html.twig")
     */
    public function uploadCardioAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $security = $this->get('security.context');
        if ($user->getUsername() != 'a.sunaev@vidal.ru' && !$security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $params = array('title' => 'Загрузка архива кардио');
        $fileName = 'encrypt.vidal.cardio.zip';
        $fileNameAndroid = 'cardio_android.zip';
        $fileNameNeuro = 'neuro.zip';
        $fileNameEndocrino = 'endocrino.zip';
        $fileNameGyneco = 'gyneco.zip';
        $fileNameVeterinary = 'veterinary.zip';
        $dir = $this->container->getParameter('archive_dir');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var KeyValue $key */
        $key = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY);
        /** @var KeyValue $keyPart */
        $keyPart = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_PART);
        /** @var KeyValue $keyPart */
        $keyNeuro = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_NEURO);
        /** @var KeyValue $keyPart */
        $keyEndocrino = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_ENDOCRINO);
        /** @var KeyValue $keyPart */
        $keyGyneco = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_GYNECO);
        /** @var KeyValue $keyPart */
        $keyVeterinary = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_VETERINARY);

        if ($request->isMethod('POST')) {
            $key->setValue($request->request->get(KeyValue::API_KEY, ''));
            $keyPart->setValue($request->request->get(KeyValue::API_KEY_PART, ''));
            $keyNeuro->setValue($request->request->get(KeyValue::API_KEY_NEURO, ''));
            $keyEndocrino->setValue($request->request->get(KeyValue::API_KEY_ENDOCRINO, ''));
            $keyGyneco->setValue($request->request->get(KeyValue::API_KEY_GYNECO, ''));
            $keyVeterinary->setValue($request->request->get(KeyValue::API_KEY_VETERINARY, ''));

            $em->flush();

            if ($file = $request->files->get('file')) {
                /** @var $file UploadedFile */
                $file->move($dir, $fileName);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileAndroid = $request->files->get('fileAndroid')) {
                /** @var $file UploadedFile */
                $fileAndroid->move($dir, $fileNameAndroid);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileNeuro = $request->files->get('fileNeuro')) {
                /** @var $file UploadedFile */
                $fileNeuro->move($dir, $fileNameNeuro);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileEndocrino = $request->files->get('fileEndocrino')) {
                /** @var $file UploadedFile */
                $fileEndocrino->move($dir, $fileNameEndocrino);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileGyneco = $request->files->get('fileGyneco')) {
                /** @var $file UploadedFile */
                $fileGyneco->move($dir, $fileNameGyneco);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileVeterinary = $request->files->get('fileVeterinary')) {
                /** @var $file UploadedFile */
                $fileVeterinary->move($dir, $fileNameVeterinary);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            $this->get('session')->getFlashBag()->add('saved', '');

            return $this->redirect($this->generateUrl('upload_cardio'));
        }

        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileName;
        $fileAndroid = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameAndroid;
        $fileNeuro = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameNeuro;
        $fileEndocrino = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameEndocrino;
        $fileGyneco = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameGyneco;
        $fileVeterinary = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameVeterinary;

        $params['post_max_size'] = ini_get('post_max_size');
        $params['upload_max_filesize'] = ini_get('upload_max_filesize');

        $params['key'] = $key;
        $params['keyPart'] = $keyPart;
        $params['keyNeuro'] = $keyNeuro;
        $params['keyEndocrino'] = $keyEndocrino;
        $params['keyGyneco'] = $keyGyneco;
        $params['keyVeterinary'] = $keyVeterinary;

        $params['modified'] = filemtime($file);
        $params['filesize'] = $this->human_filesize(filesize($file));

        $params['modifiedAndroid'] = @filemtime($fileAndroid);
        $params['filesizeAndroid'] = @$this->human_filesize(filesize($fileAndroid));

        $params['modifiedNeuro'] = @filemtime($fileNeuro);
        $params['filesizeNeuro'] = @$this->human_filesize(filesize($fileNeuro));

        $params['modifiedEndocrino'] = @filemtime($fileEndocrino);
        $params['filesizeEndocrino'] = @$this->human_filesize(filesize($fileEndocrino));

        $params['modifiedGyneco'] = @filemtime($fileGyneco);
        $params['filesizeGyneco'] = @$this->human_filesize(filesize($fileGyneco));

        $params['modifiedVeterinary'] = @filemtime($fileVeterinary);
        $params['filesizeVeterinary'] = @$this->human_filesize(filesize($fileVeterinary));

        return $params;
    }

    /**
     * @Route("/upload_debug", name="upload_debug")
     *
     * @Template("VidalMainBundle:Api:upload_cardio.html.twig")
     */
    public function uploadDebugAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $security = $this->get('security.context');
        if ($user->getUsername() != 'a.sunaev@vidal.ru' && !$security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $params = array('title' => 'Загрузка архива кардио');
        $fileName = 'debug_encrypt.vidal.cardio.zip';
        $fileNameAndroid = 'debug_cardio_android.zip';
        $fileNameNeuro = 'debug_neuro.zip';
        $fileNameEndocrino = 'debug_endocrino.zip';
        $fileNameGyneco = 'debug_gyneco.zip';
        $fileNameVeterinary = 'debug_veterinary.zip';
        $dir = $this->container->getParameter('archive_dir');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var KeyValue $key */
        $key = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY);
        /** @var KeyValue $keyPart */
        $keyPart = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_PART);
        /** @var KeyValue $keyPart */
        $keyNeuro = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_NEURO);
        /** @var KeyValue $keyPart */
        $keyEndocrino = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_ENDOCRINO);
        /** @var KeyValue $keyPart */
        $keyGyneco = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_GYNECO);
        /** @var KeyValue $keyPart */
        $keyVeterinary = $em->getRepository('VidalMainBundle:KeyValue')->getByKey(KeyValue::API_KEY_VETERINARY);

        if ($request->isMethod('POST')) {
            $key->setValue($request->request->get(KeyValue::API_KEY, ''));
            $keyPart->setValue($request->request->get(KeyValue::API_KEY_PART, ''));
            $keyNeuro->setValue($request->request->get(KeyValue::API_KEY_NEURO, ''));
            $keyEndocrino->setValue($request->request->get(KeyValue::API_KEY_ENDOCRINO, ''));
            $keyGyneco->setValue($request->request->get(KeyValue::API_KEY_GYNECO, ''));
            $keyVeterinary->setValue($request->request->get(KeyValue::API_KEY_VETERINARY, ''));

            $em->flush();

            if ($file = $request->files->get('file')) {
                /** @var $file UploadedFile */
                $file->move($dir, $fileName);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileAndroid = $request->files->get('fileAndroid')) {
                /** @var $file UploadedFile */
                $fileAndroid->move($dir, $fileNameAndroid);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileNeuro = $request->files->get('fileNeuro')) {
                /** @var $file UploadedFile */
                $fileNeuro->move($dir, $fileNameNeuro);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileEndocrino = $request->files->get('fileEndocrino')) {
                /** @var $file UploadedFile */
                $fileEndocrino->move($dir, $fileNameEndocrino);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileGyneco = $request->files->get('fileGyneco')) {
                /** @var $file UploadedFile */
                $fileGyneco->move($dir, $fileNameGyneco);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            if ($fileVeterinary = $request->files->get('fileVeterinary')) {
                /** @var $file UploadedFile */
                $fileVeterinary->move($dir, $fileNameVeterinary);
                $this->get('session')->getFlashBag()->add('notice', '');
            }

            $this->get('session')->getFlashBag()->add('saved', '');

            return $this->redirect($this->generateUrl('upload_debug'));
        }

        $file = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileName;
        $fileAndroid = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameAndroid;
        $fileNeuro = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameNeuro;
        $fileEndocrino = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameEndocrino;
        $fileGyneco = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameGyneco;
        $fileVeterinary = $this->container->getParameter('archive_dir') . DIRECTORY_SEPARATOR . $fileNameVeterinary;

        $params['post_max_size'] = ini_get('post_max_size');
        $params['upload_max_filesize'] = ini_get('upload_max_filesize');

        $params['key'] = $key;
        $params['keyPart'] = $keyPart;
        $params['keyNeuro'] = $keyNeuro;
        $params['keyEndocrino'] = $keyEndocrino;
        $params['keyGyneco'] = $keyGyneco;
        $params['keyVeterinary'] = $keyVeterinary;

        $params['modified'] = filemtime($file);
        $params['filesize'] = $this->human_filesize(filesize($file));

        $params['modifiedAndroid'] = @filemtime($fileAndroid);
        $params['filesizeAndroid'] = @$this->human_filesize(filesize($fileAndroid));

        $params['modifiedNeuro'] = @filemtime($fileNeuro);
        $params['filesizeNeuro'] = @$this->human_filesize(filesize($fileNeuro));

        $params['modifiedEndocrino'] = @filemtime($fileEndocrino);
        $params['filesizeEndocrino'] = @$this->human_filesize(filesize($fileEndocrino));

        $params['modifiedGyneco'] = @filemtime($fileGyneco);
        $params['filesizeGyneco'] = @$this->human_filesize(filesize($fileGyneco));

        $params['modifiedVeterinary'] = @filemtime($fileVeterinary);
        $params['filesizeVeterinary'] = @$this->human_filesize(filesize($fileVeterinary));

        return $params;
    }

    private function human_filesize($bytes, $decimals = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

<?php

namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\MainBundle\Entity\City;
use Vidal\MainBundle\Entity\Specialty;
use Vidal\MainBundle\Entity\UploadUsers;
use Vidal\MainBundle\Entity\User;

class UploadUsersLoadCommand extends ContainerAwareCommand
{
    /** @var City */
    protected $blankCity;

    /** @var Specialty */
    protected $blankSpecialty;

    /** @var EntityManager */
    protected $em;

    /** @var UploadUsers */
    protected $model;

    protected function configure()
    {
        $this->setName('vidal:upload_users_load');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        # снимаем ограничение времени выполнения скрипта (в safe-mode не работает)
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $container = $this->getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        /** @var UploadUsers[] $models */
        $models = $em->getRepository('VidalMainBundle:UploadUsers')->findToLoad();

        $this->em = $em;
        $this->blankCity = $em->getRepository('VidalMainBundle:City')->getBlankCity();
        $this->blankSpecialty = $em->getRepository('VidalMainBundle:Specialty')->getBlankSpecialty();

        foreach ($models as $model) {
            $output->writeln('--- vidal:upload_users_load. ' . $model->getDeliveryId());
            $model->setStatus(UploadUsers::STATUS_LOADING);
            $em->flush($model);

            $this->model = $model;
            $rows = $model->getRawDecode();
            $total = count($rows);

            # обнуляем авторегистрацию для всех
            $em->getRepository('VidalMainBundle:User')->resetAutoregister();

            # проводим все операции в базу данных
            for ($i = 0; $i < count($rows); $i++) {
                try {
                    $row = $rows[$i];
                    $step = $i + 1;
                    $output->writeln("... $step / $total");
                    $this->importRow($row);
                }
                catch (\Exception $e) {
                    $errorMsg = '[Ошибка] импорта строки #' . ($i + 1) . ': '
                        . json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        . ' -- [Отладка] ' .  $e->getMessage();
                    $output->writeln($errorMsg);
                    $model->setError($errorMsg);
                }
            }

            $model->setStatus(UploadUsers::STATUS_LOADED);
            $em->flush($model);
            $output->writeln('+++ vidal:upload_users_load completed! ' . $model->getDeliveryId());
        }
    }

    private function importRow(array $row)
    {
        $fio = trim($row[UploadUsers::FIELD_FIO]);
        $org = trim($row[UploadUsers::FIELD_ORG]);
        $city = trim($row[UploadUsers::FIELD_CITY]);
        $job = trim($row[UploadUsers::FIELD_JOB]);
        $spec = trim($row[UploadUsers::FIELD_SPEC]);
        $spec2 = trim($row[UploadUsers::FIELD_SPEC2]);
        $phone = trim($row[UploadUsers::FIELD_PHONE]);
        $email = trim($row[UploadUsers::FIELD_EMAIL]);

        /** @var User $user */
        if ($user = $this->em->getRepository('VidalMainBundle:User')->findOneByUsername($email)) {
            $userFound = true;
            $user->setAutoregister(true);
        }
        else {
            $userFound = false;
            $user = new User;
            $user->setUsername($email);
            $user->setAutoregister(true);
        }

        # FIO
        $names = explode(' ', $fio);
        $lastName = $names[0];

        if (strpos($fio, '.') !== false) {
            $secondNames = str_replace(' ', '', $names[1]);
            $secondNames = trim($secondNames, '.');
            $secondNames = explode('.', $secondNames);
            $firstName = $secondNames[0] . '.';
            $surName = $secondNames[1] . '.';
        }
        else {
            $firstName = isset($names[1]) ? $names[1] : null;
            $surName = isset($names[2]) ? $names[2] : null;
        }

        if (!$userFound) {
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setSurName($surName);
        }

        # Password
        $digits = 4;
        $pw = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        if (!$userFound) {
            $user->setPassword($pw);
            $user->hashPassword();
        }

        # Phone
        if (!empty($phone)) {
            $user->setPhone($phone);
        }

        # Job
        if (!empty($job)) {
            $user->setJobPosition($job);
        }
        if (!empty($org)) {
            $user->setJobPlace($job);
        }

        # Specialty
        if (!empty($spec) && $specialty = $this->em->getRepository('VidalMainBundle:Specialty')->findByName($spec)) {
            $user->setPrimarySpecialty($specialty);
        }
        else {
            $user->setPrimarySpecialty($this->blankSpecialty);
            $noSpecialty = $this->model->getNoSpecialty() . ' -- ' . $spec;
            $this->model->setNoSpecialty($noSpecialty);
            $this->model->addNoCityTotal();
        }

        # Specialty-2
        if (!empty($spec2) && $specialty = $this->em->getRepository('VidalMainBundle:Specialty')->findByName($spec2)) {
            $user->setSecondarySpecialty($specialty);
        }

        # City
        if ($city) {
            if ($cityEntity = $this->em->getRepository('VidalMainBundle:City')->findByName($city)) {
                $user->setCity($cityEntity);
            }
            elseif ($cityEntity = $this->em->getRepository('VidalMainBundle:City')->findAnyByName($city)) {
                $user->setCity($cityEntity);
            }
            else {
                $user->setCity($this->blankCity);
                $noCity = $this->model->getNoCity() . ' -- ' . $city;
                $this->model->setNoCity($noCity);
                $this->model->addNoCityTotal();
            }
        }
        else {
            $user->setCity($this->blankCity);
        }

        if ($userFound == false) {
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}

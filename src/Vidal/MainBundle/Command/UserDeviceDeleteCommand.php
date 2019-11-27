<?php
namespace Vidal\MainBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vidal\DrugBundle\Entity\Publication;

class UserDeviceDeleteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:user_device_delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:user_device_delete started');
        $publicationId = '6073';

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /** @var EntityManager $emDrug */
        $emDrug = $this->getContainer()->get('doctrine')->getManager('drug');
        /** @var EntityManager $emMain */
        $emMain = $this->getContainer()->get('doctrine')->getManager();
        $pdo = $emMain->getConnection();

        $sql = "delete from user_device WHERE androidId IS NULL OR androidId = ''";
        $pdo->prepare($sql)->execute();

        $sql = 'delete ud1 from user_device ud1, user_device ud2 WHERE ud1.id > ud2.id AND ud1.androidId = ud2.androidId';
        $pdo->prepare($sql)->execute();

        /** @var Publication $publication */
        $publication = $emDrug->getRepository('VidalDrugBundle:Publication')->findOneById($publicationId);

        if (!$publication) {
            return;
        }

        $deviceGroups = $emMain->getRepository('VidalMainBundle:User')->findDevicesGrouped();

        foreach ($deviceGroups as $gcm => $devices) {
            foreach ($devices as $androidId) {
                if ($this->send(array($androidId), $gcm, $publication)) {
                    $output->writeln('... success');
                }
                else {
                    $sql = "delete from user_device WHERE androidId = '$androidId'";
                    $pdo->prepare($sql)->execute();
                    $output->writeln('... deleted androidId: ' . $androidId);
                }
            }
        }

        $output->writeln("+++ vidal:user_device_delete completed!");
    }

    private function send($deviceIds, $gcmKey, Publication $publication)
    {
        $fields = array(
            "registration_ids" => $deviceIds,
            "notification" => array(
                'body' => $this->strip($publication->getAnnounce()),
                'title' => $this->strip($publication->getTitle()),
                'badge' => 1,
                'sound' => 'default',
                'id' => $publication->getId(),
            ),
        );

        $fields = json_encode($fields);

        $headers = array(
            'Authorization: key=' . $gcmKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

        $result = curl_exec($ch);
        curl_close($ch);

        if (strpos($result, 'NotRegistered') !== false) {
            return false;
        }

        return true;
    }

    private function strip($string)
    {
        $string = strip_tags(html_entity_decode($string, ENT_QUOTES, 'UTF-8'));

        return trim(str_replace(explode(' ', '® ™'), '', $string));
    }
}
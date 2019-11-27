<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Vidal\MainBundle\Entity\AstrazenecaRegion;
use Vidal\MainBundle\Entity\AstrazenecaMap;

class AddressToCoordsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:parse_address');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:parse_address started');

        $em = $this->getContainer()->get('doctrine')->getManager();


        if (($handle = fopen("/var/www/vidal/web/astrazenecaMap.csv", "r")) !== FALSE) {
            $i = 0;
            while (($data = fgetcsv($handle, 0, "|",'"')) !== FALSE) {
                if ($data[2] == '<>' || $data[2] == '-') $data[2] = '';
                if ($data[1] == '<>' || $data[1] == '-') $data[1] = '';
                if ($data[0] == '<>' || $data[0] == '-') $data[0] = '';

                $params = array(
                    'geocode' => $data[2].' ,'.$data[0].' ,'.$data[1],// адрес
                    'format'  => 'json',                          // формат ответа
                    'results' => 1,                               // количество выводимых результатов
                    'key'     => 'AGVefFMBAAAANZrqAQIA7ooTl8iKd1hoNAGFRqMGezGqEV0AAAAAAAAAAAD_DMf2rA3d0pjQ49z8ShAZ6dEw7A==',                           // ваш api key
                );
                $response = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

                if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
                {
                    $region = $em->getRepository('VidalMainBundle:AstrazenecaRegion')->findOneByTitle($data[2]);
                    if ($region == null){
                        $region = new AstrazenecaRegion();
                        $region->setTitle($data[2]);
                        $region->setLatitude(0);
                        $region->setLongitude(0);
                        $region->setZoom(0);
                        $em->persist($region);
                        $em->flush();
                        $em->refresh($region);
                    }

                    $coords = $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
                    $coords = explode(' ',$coords);

                    $hospital = new AstrazenecaMap();
                    $hospital->setRegion($region);
                    $hospital->setTitle($data[3]);
                    $hospital->setHospitalType($data[4]);
                    $hospital->setAdr($data[2].' ,'.$data[0].' ,'.$data[1]);
                    $hospital->setOfferId(0);
                    $hospital->setLatitude($coords[0]);
                    $hospital->setLongitude($coords[1]);
                    $em->persist($hospital);
                    $em->flush();
                    $em->refresh($hospital);

                    $output->writeln($response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                }
                else
                {
                    $output->writeln('<error>--- not found </error>');
                }
                $i++;
                if ($i % 50 == 0){
                    $output->writeln('<error>--- Ожидание </error>');
                    sleep(rand(5,10));
                }

            }
            fclose($handle);
        }

        $output->writeln('--- vidal:parse_coords finished');
    }
}
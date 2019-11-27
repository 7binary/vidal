<?php
namespace Vidal\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Vidal\MainBundle\Entity\AstrazenecaRegion;
use Vidal\MainBundle\Entity\AstrazenecaMap;

class AddressToCoords2Command extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:parse_address_2');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('--- vidal:parse_address started');

        $em = $this->getContainer()->get('doctrine')->getManager();

        $regions = $em->getRepository('VidalMainBundle:AstrazenecaRegion')->findAll();
        if ($regions) {
            $i = 0;
            foreach ( $regions as $region ){

                $params = array(
                    'geocode' => $region->getTitle(),// адрес
                    'format'  => 'json',                          // формат ответа
                    'results' => 1,                               // количество выводимых результатов
                    'key'     => 'AGVefFMBAAAANZrqAQIA7ooTl8iKd1hoNAGFRqMGezGqEV0AAAAAAAAAAAD_DMf2rA3d0pjQ49z8ShAZ6dEw7A==',                           // ваш api key
                );
                $response = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

                if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
                {

                    $coords = $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
                    $coords = explode(' ',$coords);

                    $region->setLatitude($coords[0]);
                    $region->setLongitude($coords[1]);
                    $region->setZoom(0);
                    $em->flush();

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
        }

        $output->writeln('--- vidal:parse_coords finished');
    }
}
<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProtecIpRepository extends EntityRepository
{
    /**
     * @param string $ip
     * @return ProtecIp
     */
    public function get($ip)
    {
        $protecIp = $this->_em->createQuery("
            SELECT i
            FROM VidalMainBundle:ProtecIp i
            WHERE i.ip = :ip
        ")->setParameter('ip', $ip)
            ->getOneOrNullResult();

        if ($protecIp == null) {
            $url = "http://ipgeobase.ru:7020/geo?ip=" . $ip;
            $xml = simplexml_load_file($url);
            $regionTitle = (string) $xml->ip->region;
            $countryCode = (string) $xml->ip->country;

            $protecIp = new ProtecIp();
            $protecIp->setIp($ip);
            $protecIp->setRegion($regionTitle);
            $protecIp->setCountry($countryCode);

            $this->_em->persist($protecIp);
            $this->_em->flush();
        }

        return $protecIp;
    }
}
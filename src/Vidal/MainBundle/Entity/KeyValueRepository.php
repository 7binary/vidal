<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class KeyValueRepository extends EntityRepository
{
    /**
     * @param string $key
     * @return KeyValue
     */
    public function getByKey($key)
    {
        $keyValue = $this->_em->createQuery('
		 	SELECT k
		 	FROM VidalMainBundle:KeyValue k
		 	WHERE k.k = :key
		')->setParameter('key', $key)
            ->getOneOrNullResult();

        if ($keyValue === null) {
            $keyValue = new KeyValue();
            $keyValue->setKey($key);
            $keyValue->setValue(null);
            $this->_em->persist($keyValue);
            $this->_em->flush($keyValue);
            $this->_em->refresh($keyValue);
        }

        return $keyValue;
    }

    public function getApiKeyValue()
    {
        return $this->getByKey(KeyValue::API_KEY);
    }


    public function getApiValue()
    {
        $keyValue = $this->getByKey(KeyValue::API_KEY);

        return $keyValue->getValue();
    }

    public function getXTokenValue()
    {
        $keyValue = $this->getByKey(KeyValue::XTOKEN);

        return $keyValue->getValue();
    }

    public function checkMatch($key, $value)
    {
        $keyValue = $this->_em->createQuery('
		 	SELECT k
		 	FROM VidalMainBundle:KeyValue k
		 	WHERE k.k = :key
		')->setParameter('key', $key)
            ->getOneOrNullResult();

        if (null == $keyValue) {
            return false;
        }

        return $value === $keyValue->getValue();
    }
}
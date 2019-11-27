<?php
namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MoleculeNameRepository extends EntityRepository
{
    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT m.MoleculeNameID, m.RusName, m.EngName
			FROM VidalDrugBundle:MoleculeName m
			WHERE m.MoleculeNameID LIKE :id
				OR m.RusName LIKE :RusName
				OR m.EngName LIKE :RusName
			ORDER BY m.MoleculeID ASC
		')->setParameter('id', $term . '%')
            ->setParameter('RusName', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['MoleculeNameID'],
                'text' => $code['MoleculeNameID'] . ' - ' . (empty($code['RusName']) ? $code['EngName'] : $code['RusName'])
            );
        }

        return $data;
    }

    public function findOneByMoleculeNameID($id)
    {
        return $this->_em->createQuery('
            SELECT m
            FROM VidalDrugBundle:MoleculeName m
            WHERE m.MoleculeNameID = :id
        ')->setParameter('id', $id)
			->getOneOrNullResult();
    }
}
<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MoleculeRepository extends EntityRepository
{
    /**
     * @param Molecule[] $molecules
     * @return array
     */
    public function findGa($molecules)
    {
        $moleculeIds = array();
        foreach ($molecules as $molecule) {
            $moleculeIds[] = $molecule->getMoleculeID();
        }

        return $this->_em->createQuery('
			SELECT SUM(p.ga_pageviews)
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID IN (
			  	SELECT DISTINCT p2.ProductID
			    FROM VidalDrugBundle:Product p2
			    LEFT JOIN p2.document d
			   	LEFT JOIN p2.moleculeNames mn
                LEFT JOIN VidalDrugBundle:Molecule m WITH m = mn.MoleculeID
                WHERE m IN (:moleculeIds)
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p2.MarketStatusID IN (1,2,7)
                    AND p2.ProductTypeCode NOT IN (\'SUBS\')
                    AND p2.inactive = FALSE
                    AND p2.IsNotForSite = FALSE
                    AND p2.parent IS NULL
                    AND p2.MainID IS NULL
			)
		')->setParameter('moleculeIds', $moleculeIds)
            ->getSingleScalarResult();
    }

    /**
     * @param Molecule[] $molecules
     * @return array
     */
    public function findGaCount($molecules)
    {
        $moleculeIds = array();
        foreach ($molecules as $molecule) {
            $moleculeIds[] = $molecule->getMoleculeID();
        }

        return $this->_em->createQuery('
			SELECT COUNT(DISTINCT p.ProductID)
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.moleculeNames mn
			LEFT JOIN VidalDrugBundle:Molecule m WITH m = mn.MoleculeID
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE m IN (:moleculeIds)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('moleculeIds', $moleculeIds)
            ->getSingleScalarResult();
    }

    public function findGrouped()
    {
        $pdo = $this->_em->getConnection();

        $stmt = "
			SELECT m.MoleculeID, m.LatName, m.RusName, d.DocumentID, d.ArticleID
			FROM molecule m
			LEFT JOIN molecule_document md ON md.MoleculeID = m.MoleculeID
			LEFT JOIN document d ON d.DocumentID = md.DocumentID
			ORDER BY FIELD(d.DocumentID, 2, 5, 1, 4, 3, 6, 7, 8)
		";

        $stmt = $pdo->prepare($stmt);
        $stmt->execute();
        $raw = $stmt->fetchAll();

        $molecules = array();

        foreach ($raw as $r) {
            $latName = mb_strtolower($r['LatName'], 'utf-8');
            $rusName = mb_strtolower($r['RusName'], 'utf-8');

            if (!empty($latName) && !isset($molecules[$latName])) {
                $molecules[$latName] = $r;
            }
            if (!empty($rusName) && !isset($molecules[$rusName])) {
                $molecules[$rusName] = $r;
            }
        }

        return $molecules;
    }

    public function findByName($name)
    {
        return $this->_em->createQuery('
			SELECT m.MoleculeID
		 	FROM VidalDrugBundle:Molecule m
		 	WHERE m.LatName = :name
		')->setParameter('name', $name)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findOneByMoleculeID($MoleculeID)
    {
        return $this->_em->createQuery('
		 	SELECT m
		 	FROM VidalDrugBundle:Molecule m
		 	WHERE m = :MoleculeID
		')->setParameter('MoleculeID', $MoleculeID)
            ->getOneOrNullResult();
    }

    public function findByMoleculeID($MoleculeID)
    {
        return $this->_em->createQuery('
		 	SELECT m
		 	FROM VidalDrugBundle:Molecule m
		 	WHERE m = :MoleculeID
		')->setParameter('MoleculeID', $MoleculeID)
            ->getOneOrNullResult();
    }

    public function findByProductID($ProductID)
    {
        $molecules = $this->_em->createQuery('
			SELECT m
			FROM VidalDrugBundle:Molecule m
			JOIN m.moleculeNames mn
			JOIN mn.products p
			WHERE p.ProductID = :ProductID
		')->setParameter('ProductID', $ProductID)
            ->getResult();

        # если веществ больше 3, то их не отображают
        if (count($molecules) > 3) {
            return array();
        }

        # если среди них хотя бы одно запрещенное - не отображают
        foreach ($molecules as $molecule) {
            if (in_array($molecule->getMoleculeID(), array(1144, 2203))) {
                return array();
            }
        }

        return $molecules;
    }

    public function findByArticle($articleId)
    {
        return $this->_em->createQuery('
			SELECT m
			FROM VidalDrugBundle:Molecule m
			JOIN m.documents d WITH d.ArticleID = 1
			JOIN d.nozologies n
			JOIN n.articles a
			WHERE a = :articleId
				AND m.MoleculeID NOT IN (1144,2203)
			ORDER BY m.RusName ASC
		')->setParameter('articleId', $articleId)
            ->getResult();
    }

    public function findOneByProductID($ProductID)
    {
        return $this->_em->createQuery('
			SELECT m.MoleculeID, m.LatName, m.RusName
			FROM VidalDrugBundle:Molecule m
			LEFT JOIN VidalDrugBundle:MoleculeName mn WITH mn.MoleculeID = m
			LEFT JOIN mn.products p
			WHERE p = :ProductID
		')->setParameter('ProductID', $ProductID)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function findAutocomplete()
    {
        $molecules = $this->_em->createQuery('
			SELECT DISTINCT m.RusName, m.LatName
			FROM VidalDrugBundle:Molecule m
			ORDER BY m.RusName ASC
		')->getResult();

        $moleculeNames = array();

        for ($i = 0; $i < count($molecules); $i++) {
            $patterns = array('/<SUP>.*<\/SUP>/', '/<SUB>.*<\/SUB>/');
            $replacements = array('', '');
            $RusName = preg_replace($patterns, $replacements, $molecules[$i]['RusName']);
            $RusName = mb_strtolower($RusName, 'UTF-8');
            $LatName = preg_replace($patterns, $replacements, $molecules[$i]['LatName']);
            $LatName = mb_strtolower($LatName, 'UTF-8');

            if (!empty($RusName)) {
                $moleculeNames[] = $RusName;
            }

            if (!empty($LatName)) {
                $moleculeNames[] = $LatName;
            }
        }

        $moleculeNames = array_unique($moleculeNames);
        usort($moleculeNames, 'strcasecmp');

        return $moleculeNames;
    }

    public function getOptions()
    {
        $raw = $this->_em->createQuery('
		 	SELECT m.MoleculeID, m.RusName, m.LatName
		 	FROM VidalDrugBundle:Molecule m
		 	ORDER BY m.LatName ASC
		 ')->getResult();

        $molecules = array();

        foreach ($raw as $r) {
            $molecules[] = array(
                'id' => $r['MoleculeID'],
                'title' => $r['LatName'] . ' (' . $r['RusName'] . ')'
            );
        }

        return $molecules;
    }

    public function findByQuery($q)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('m.MoleculeID, m.LatName, m.RusName, mnn.GNParent, mnn.description')
            ->from('VidalDrugBundle:Molecule', 'm')
            ->leftJoin('m.GNParent', 'mnn')
            ->orderBy('m.LatName', 'ASC');

        $words = explode(' ', $q);

        # поиск по всем словам вместе
        $qb->where($this->where($words, 'AND'));
        $results = $qb->getQuery()->getResult();

        if (!empty($results)) {
            return $results;
        }

        foreach ($words as $word) {
            if (mb_strlen($word, 'utf-8') < 3) {
                return array();
            }
        }

        # поиск по любому из слов
        $qb->where($this->where($words, 'OR'));
        $results = $qb->getQuery()->getResult();

        if (!empty($results)) {
            return $results;
        }

        return array();
    }

    private function where($words, $s)
    {
        $s = ($s == 'OR') ? ' OR ' : ' AND ';

        $where = '';

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= $s;
            }
            $where .= "(m.RusName LIKE '$word%' OR m.LatName LIKE '$word%' OR m.RusName LIKE '% $word%' OR m.LatName LIKE '% $word%')";
        }

        return $where;
    }

    public function countComponents($productIds)
    {
        $componentsRaw = $this->_em->createQuery('
			SELECT p.ProductID, COUNT(ms.MoleculeID) molecules
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.moleculeNames ms
			WHERE p.ProductID IN (:productIds)
			GROUP BY p.ProductID
		')->setParameter('productIds', $productIds)
            ->getResult();

        $components = array();

        for ($i = 0; $i < count($componentsRaw); $i++) {
            $key = $componentsRaw[$i]['ProductID'];
            $components[$key] = $componentsRaw[$i]['molecules'];
        }

        return $components;
    }

    public function findByDocuments1($documents)
    {
        $documentIds = array();

        foreach ($documents as $document) {
            if ($document['ArticleID'] == 1) {
                $documentIds[] = $document['DocumentID'];
            }
        }

        if (empty($documentIds)) {
            return array();
        }

        return $this->_em->createQuery('
			SELECT DISTINCT m.MoleculeID, m.LatName, m.RusName, mnn.GNParent, mnn.description, d.DocumentID
			FROM VidalDrugBundle:Molecule m
			JOIN m.documents d
			LEFT JOIN m.GNParent mnn
			WHERE d IN (:documentIds)
			ORDER BY m.RusName ASC
		')->setParameter('documentIds', $documentIds)
            ->getResult();
    }

    public function getNames()
    {
        $names = array();

        $molecules = $this->_em->createQuery('
		 	SELECT m.LatName
		 	FROM VidalDrugBundle:Molecule m
		 	ORDER BY m.LatName ASC
		')->getResult();

        foreach ($molecules as $m) {
            $names[] = $m['LatName'];
        }

        $molecules = $this->_em->createQuery("
		 	SELECT m.RusName
		 	FROM VidalDrugBundle:Molecule m
		 	WHERE m.RusName != ''
		 	ORDER BY m.RusName ASC
		")->getResult();

        foreach ($molecules as $m) {
            $name = $m['RusName'];
            if (!empty($name)) {
                $names[] = $name;
            }
        }

        $uniques = array();

        foreach ($names as $name) {
            if (!isset($uniques[$name])) {
                $uniques[$name] = '';
            }
        }

        return array_keys($uniques);
    }

    public function getQueryByLetter($l)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('m');

        if (in_array($l, array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z'))) {
            $qb->where('m.LatName LIKE :l')
                ->setParameter('l', $l . '%')
                ->orderBy('m.LatName', 'ASC');
        }
        else {
            $qb->where('m.RusName LIKE :l')
                ->andWhere("m.RusName != ''")
                ->setParameter('l', $l . '%')
                ->orderBy('m.RusName', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    public function getQueryByString($q)
    {
        $qb = $this->_em->createQueryBuilder('m');
        $qb->select('m')
            ->from('VidalDrugBundle:Molecule', 'm')
            ->orderBy('m.RusName', 'ASC');

        # поиск по всем словам
        $where = '';
        $words = explode(' ', $q);

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(m.LatName LIKE '$word%' OR m.LatName LIKE '% $word%' OR m.RusName LIKE '$word%' OR m.RusName LIKE '% $word%')";
        }

        $qb->andWhere($where);
        $results = $qb->getQuery()->getResult();

        # поиск по одному слову
        if (empty($results)) {
            $where = '';
            for ($i = 0; $i < count($words); $i++) {
                $word = $words[$i];
                if ($i > 0) {
                    $where .= ' OR ';
                }
                $where .= "(m.LatName LIKE '$word%' OR m.LatName LIKE '% $word%' OR m.RusName LIKE '$word%' OR m.RusName LIKE '% $word%')";
            }

            $results = $qb->getQuery()->getResult();
        }

        return $results;
    }

    public function getQuery()
    {
        return $this->_em->createQuery("
		 	SELECT m
		 	FROM VidalDrugBundle:Molecule m
		 	WHERE m.RusName != ''
		 	ORDER BY m.RusName ASC
		");
    }

    public function findByNozologyCode($Code)
    {
        return $this->_em->createQuery('
		 	SELECT m
		 	FROM VidalDrugBundle:Molecule m
		 	JOIN m.documents d WITH d.ArticleID = 1
		 	JOIN d.nozologies n
		 	WHERE n.Code = :Code
		 		AND m.MoleculeID NOT IN (1144,2203)
		')->setParameter('Code', $Code)
            ->getResult();
    }

    public function findByProductIds($productIds)
    {
        $raw = $this->_em->createQuery('
			SELECT DISTINCT m.MoleculeID, m.RusName, m.LatName
			FROM VidalDrugBundle:Molecule m
			JOIN m.moleculeNames mn
			JOIN mn.products p
			WHERE p.ProductID IN (:productIds)
				AND m.MoleculeID NOT IN (1144, 2203)
			ORDER BY m.MoleculeID ASC
		')->setParameter('productIds', $productIds)
            ->getResult();

        $molecules = array();

        foreach ($raw as $r) {
            $key = $r['MoleculeID'];
            $molecules[$key] = $r;
        }

        return $molecules;
    }

    # надо получить список идентификаторов молекул у этого продукта
    public function idsByProduct($ProductID)
    {
        $raw = $this->_em->createQuery('
			SELECT m.MoleculeID
			FROM VidalDrugBundle:Molecule m
			JOIN m.moleculeNames mn
			JOIN mn.products p
			WHERE p.ProductID = :ProductID
			ORDER BY m.MoleculeID ASC
		')->setParameter('ProductID', $ProductID)
            ->getResult();

        $moleculeIds = array();

        foreach ($raw as $r) {
            $moleculeIds[] = $r['MoleculeID'];
        }

        return $moleculeIds;
    }

    public function findByClPhPointerID($ClPhPointerID)
    {
        $raw = $this->_em->createQuery('
			SELECT m.MoleculeID, m.LatName, d.DocumentID, d.ArticleID
			FROM VidalDrugBundle:Molecule m
			JOIN m.documents d
			JOIN d.clphPointers pointer
			WHERE pointer = :id
				AND m.MoleculeID != 1144
		')->setParameter('id', $ClPhPointerID)
            ->getResult();

        $molecules = array();
        $documents = array();

        foreach ($raw as $r) {
            $key = $r['MoleculeID'];

            if (!isset($molecules[$key])) {
                $molecules[$key] = $r['LatName'];
            }

            $documents[] = $r['DocumentID'];
        }

        return array($molecules, $documents);
    }

    public function findByKfu($ClPhPointerID, $moleculeIdsUsed)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('m')
            ->from('VidalDrugBundle:Molecule', 'm')
            ->join('m.documents', 'd')
            ->join('d.clphPointers', 'pointer')
            ->where('pointer = :id')
            ->andWhere('m.MoleculeID NOT IN (1144,2203)')
            ->orderBy('m.RusName', 'ASC')
            ->setParameter('id', $ClPhPointerID);

        if (!empty($moleculeIdsUsed)) {
            $qb->andWhere('m.MoleculeID NOT IN (:moleculeIdsUsed)')
                ->setParameter('moleculeIdsUsed', $moleculeIdsUsed);
        }

        return $qb->getQuery()->getResult();
    }

    public function adminAutocomplete($term)
    {
        $codes = $this->_em->createQuery('
			SELECT m.MoleculeID, m.RusName, m.LatName
			FROM VidalDrugBundle:Molecule m
			WHERE m.MoleculeID LIKE :id
				OR m.RusName LIKE :RusName
				OR m.LatName LIKE :RusName
			ORDER BY m.MoleculeID ASC
		')->setParameter('id', $term . '%')
            ->setParameter('RusName', '%' . $term . '%')
            ->setMaxResults(15)
            ->getResult();

        $data = array();

        foreach ($codes as $code) {
            $data[] = array(
                'id' => $code['MoleculeID'],
                'text' => $code['MoleculeID'] . ' - ' . (empty($code['RusName']) ? $code['LatName'] : $code['RusName'])
            );
        }

        return $data;
    }
}
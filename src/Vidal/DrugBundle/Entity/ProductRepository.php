<?php

namespace Vidal\DrugBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    protected $atcCodes = array();

    public function findSame($rusName2)
    {
        $articleIds = array(2, 5, 4, 1);

        foreach ($articleIds as $articleId) {
            $product = $this->_em->createQuery("
                SELECT p
                FROM VidalDrugBundle:Product p
                JOIN p.document d WITH d.ArticleID = :articleId
                WHERE p.MarketStatusID IN (1,2,7)
                    AND p.ProductTypeCode = 'DRUG'
                    AND p.inactive = FALSE
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p.IsNotForSite = FALSE
                    AND p.parent IS NULL
                    AND p.MainID IS NULL
                    AND p.RusName LIKE :rusName2
            ")->setParameter('rusName2', $rusName2 . '%')
                ->setParameter('articleId', $articleId)
                ->setMaxResults(1)
                ->getOneOrNullresult();

            if ($product) {
                return $product;
            }
        }

        return null;
    }

    public function findBatchItem($from, $limit = 100)
    {
        $pdo = $this->_em->getConnection();

        # Препараты
        $productsRaw = $this->_em->createQuery("
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus,
				p.Composition, p.multiForm,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.url, p.uri, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ClPhGrDescription, d.CompiledComposition
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
		")->setMaxResults($limit)
            ->setFirstResult($from - 1)
            ->getResult();

        $products = array();

        foreach ($productsRaw as $p) {
            $p['atc'] = array();
            $p['molecules'] = array();
            $p['infoPages'] = array();
            $p['distributors'] = array();
            $p['owner'] = null;

            if (!empty($p['forms'])) {
                $p['Composition'] = $p['CompiledComposition'];
            }
            unset($p['CompiledComposition']);

            $key = $p['ProductID'];
            $products[$key] = $p;
        }

        # АТХ
        $productAtc = $this->_em->createQuery('
			SELECT p.ProductID, a.ATCCode, a.RusName, a.EngName, a.ParentATCCode, a.Level
			FROM VidalDrugBundle:ATC a
			JOIN a.products p
			ORDER BY a.ATCCode ASC
		')->getResult();

        foreach ($productAtc as $item) {
            $key = $item['ProductID'];
            unset($item['ProductID']);
            $products[$key]['atc'][] = $item;
        }

        # Вещества
        $productMolecule = $this->_em->createQuery('
			SELECT m.MoleculeID, m.RusName, m.LatName, p.ProductID
			FROM VidalDrugBundle:Molecule m
			JOIN m.moleculeNames mn
			JOIN mn.products p
			WHERE m.MoleculeID NOT IN (1144, 2203)
			ORDER BY m.MoleculeID ASC
		')->getResult();

        foreach ($productMolecule as $item) {
            $key = $item['ProductID'];
            unset($item['ProductID']);
            $products[$key]['molecules'][] = $item;
        }

        # Документы - Представительства
        $stmt = $pdo->prepare("
             SELECT i.InfoPageID, i.RusName, i.EngName, i.RusAddress,
                i.Notes, i.PhoneNumber, i.countProducts, co.RusName Country, d.DocumentID,
                pic.PathForElectronicEdition path
             FROM infopage i  
             JOIN document_infopage di ON di.InfoPageID = i.InfoPageID
             JOIN document d ON d.DocumentID = di.DocumentID
             LEFT JOIN country co ON co.CountryCode = i.CountryCode
             LEFT JOIN infopage_picture ip ON ip.InfoPageID = i.InfoPageID
             LEFT JOIN picture pic ON pic.PictureID = ip.PictureID
             WHERE i.CountryEditionCode = 'RUS'
        ");
        $stmt->execute();
        $documentInfoPage = $stmt->fetchAll();

        $documents = array();
        foreach ($documentInfoPage as $di) {
            $key = $di['DocumentID'];
            if (!isset($documents[$key])) {
                $documents[$key] = array('infoPages' => array());
            }
            $di['pictureUrl'] = 'https://www.vidal.ru/upload/companies/'
                . strtolower(preg_replace('/.+\\\\JPG\\\\/', '', $di['path']));
            unset($di['DocumentID']);
            unset($di['path']);
            $documents[$key]['infoPages'][] = $di;
        }

        foreach ($products as $ProductID => &$p) {
            if (!empty($p['DocumentID'])) {
                $key = $p['DocumentID'];
                if (isset($documents[$key]) && !empty($documents[$key]['infoPages'])) {
                    $p['infoPages'] = $documents[$key]['infoPages'];
                }
            }
        }

        $products = array_values($products);

        return $products[0];
        return $products;
    }

    public function findBatchList()
    {
        return $this->_em->createQuery("
            SELECT p.ProductID
            FROM VidalDrugBundle:Product p
            LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
            LEFT JOIN p.document d
            WHERE p.MarketStatusID IN (1,2,7)
                AND p.ProductTypeCode NOT IN ('SUBS')
                AND p.inactive = FALSE
                AND (d.inactive IS NULL OR d.inactive = FALSE)
                AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                AND p.IsNotForSite = FALSE
                AND p.parent IS NULL
                AND p.MainID IS NULL
        ")->getResult();
    }

    public function findFieldsByProductID($ProductID)
    {
        return $this->_em->createQuery("
			SELECT DISTINCT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.url, p.uri, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ClPhGrDescription
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.ProductID = :ProductID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
		")->setParameter('ProductID', $ProductID)
            ->getOneOrNullResult();
    }

    public function findByProductID($ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p = :ProductID
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $ProductID)
            ->getOneOrNullresult();
    }

    public function findOneById($ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			WHERE p.ProductID = :ProductID
		")->setParameter('ProductID', $ProductID)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findByUrlOrName($name)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE (p.url = :name OR p.Name =:name)
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('name', $name)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findByUrl($url)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.url = :url
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('url', $url)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findByUri($uri)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.uri = :uri
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('uri', $uri)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findByName($name)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.Name = :name
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('name', $name)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findByTerm($term)
    {
        $qb = $this->_em->createQueryBuilder();
        $anyOfWord = null;

        $qb->select('p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.ProductID, p.photo, p.hidePhoto, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, pt.ProductTypeCode,
				p.shortZipInfo, p.hasChildren, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms, p.RusName2, co.GDDBName, country.RusName countryName,
				d.Indication, d.ArticleID, d.DocumentID')
            ->from('VidalDrugBundle:Product', 'p')
            ->leftJoin('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->leftJoin('VidalDrugBundle:ProductType', 'pt', 'WITH', 'p.ProductTypeCode = pt.ProductTypeCode')
            ->leftJoin('VidalDrugBundle:ProductCompany', 'pc', 'WITH', 'pc.ProductID = p.ProductID AND pc.ItsMainCompany = TRUE')
            ->leftJoin('VidalDrugBundle:Company', 'co', 'WITH', 'co.CompanyID = pc.CompanyID')
            ->leftJoin('VidalDrugBundle:Country', 'country', 'WITH', 'country.CountryCode = co.CountryCode')
            ->orderBy('p.RusName2', 'ASC')
            ->where('p.MarketStatusID IN (1,2,7)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->andWhere('p.inactive = FALSE')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
            ->andWhere("p.ProductTypeCode NOT IN ('SUBS')");

        $words = explode(' ', $term);

        # поиск по всем словам вместе
        $where = '';

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(p.RusName2 LIKE '$word%' OR p.EngName LIKE '$word%' OR p.RusName2 LIKE '% $word%' OR p.EngName LIKE '% $word%' OR p.RusName2 LIKE '%-$word' OR p.EngName LIKE '%-$word')";
        }

        $qb->andWhere($where);
        $products = $qb->getQuery()->getResult();

        if (empty($products)) {
            # поиск по любому из слов, если по всем не дал результата
            # определяем, стоит ли искать по любому слову, должно быть хотя бы одно слово от 3х символов
            for ($i = 0; $i < count($words); $i++) {
                if (mb_strlen($words[$i], 'utf-8') > 2) {
                    $anyOfWord[] = $words[$i];
                }
            }

            if (empty($anyOfWord)) {
                return array();
            }

            $where = '';

            for ($i = 0; $i < count($anyOfWord); $i++) {
                $word = $anyOfWord[$i];
                if ($i > 0) {
                    $where .= ' OR ';
                }
                $where .= "(p.RusName2 LIKE '$word%' OR p.EngName LIKE '$word%' OR p.RusName2 LIKE '% $word%' OR p.EngName LIKE '% $word%' OR p.RusName2 LIKE '%-$word' OR p.EngName LIKE '%-$word')";
            }

            $products = $qb
                ->where('p.MarketStatusID IN (1,2,7)')
                ->andWhere('p.IsNotForSite = FALSE')
                ->andWhere('p.inactive = FALSE')
                ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
                ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
                ->andWhere('p.parent IS NULL')
                ->andWhere('p.MainID IS NULL')
                ->andWhere("p.ProductTypeCode NOT IN ('SUBS')")
                ->andWhere($where)
                ->orderBy('p.RusName2', 'ASC')
                ->getQuery()->getResult();
        }

        return $products;
    }

    public function findByUrlWithoutProduct($url, $ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			WHERE p.url = :url
				AND p.ProductID != :ProductID
		")->setParameter('url', $url)
            ->setParameter('ProductID', $ProductID)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findByUriWithoutProduct($uri, $ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			WHERE p.uri = :uri
				AND p.ProductID != :ProductID
		")->setParameter('uri', $uri)
            ->setParameter('ProductID', $ProductID)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findWithChildren($ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE (p = :ProductID OR p.parent = :ProductID)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $ProductID)
            ->getResult();
    }

    public function findChildren($ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.parent = :ProductID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $ProductID)
            ->getResult();
    }

    public function findSubmain($ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.MainID = :ProductID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $ProductID)
            ->getResult();
    }

    public function findFamilyProductIds($ProductID)
    {
        $productIds = array($ProductID);

        $products = $this->_em->createQuery("
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE (p.MainID = :ProductID OR p.parent = :ProductID)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $ProductID)
            ->getResult();

        if (!empty($products)) {
            foreach ($products as $p) {
                $productIds[] = $p['ProductID'];
            }
        }

        return $productIds;
    }

    public function findOneByProductID($ProductID)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p = :ProductID
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
		")->setParameter('ProductID', $ProductID)
            ->getOneOrNullresult();
    }

    public function findBadByName($name)
    {
        return $this->_em->createQuery("
			SELECT p
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			WHERE p.Name = :name
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.ProductTypeCode = 'BAD'
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
		")->setParameter('name', $name)
            ->setMaxResults(1)
            ->getOneOrNullresult();
    }

    public function findGa($from = null, $limit = 1000)
    {
        if ($from) {
            return $this->_em->createQuery("
                SELECT p.ProductID, p.url, p.uri, p.RusName, p.EngName, p.Name
                FROM VidalDrugBundle:Product p
                LEFT JOIN p.document d
                WHERE p.MarketStatusID IN (1,2,7)
                    AND p.ProductTypeCode NOT IN ('SUBS')
                    AND p.inactive = FALSE
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p.IsNotForSite = FALSE
                    AND p.ga_pageviews IS NULL
                    AND p.parent IS NULL
                    AND p.MainID IS NULL
                ORDER BY p.RusName ASC
            ")->setMaxResults($limit)
                ->setFirstResult($from - 1)
                ->getResult();
        }
        else {
            return $this->_em->createQuery("
                SELECT p.ProductID, p.url, p.uri, p.RusName, p.EngName, p.Name
                FROM VidalDrugBundle:Product p
                LEFT JOIN p.document d
                WHERE p.MarketStatusID IN (1,2,7)
                    AND p.ProductTypeCode NOT IN ('SUBS')
                    AND p.inactive = FALSE
                    AND (d.inactive IS NULL OR d.inactive = FALSE)
                    AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
                    AND p.IsNotForSite = FALSE
                    AND p.ga_pageviews IS NULL
                    AND p.parent IS NULL
                    AND p.MainID IS NULL
                ORDER BY p.RusName ASC
            ")->getResult();
        }
    }

    public function productsByDocumentsMain()
    {
        $pds = $this->_em->createQuery("
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, pp.ProductID ParentID, p.ga_pageviews, d.DocumentID, d.ArticleID, p.photo, 
				p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.MainID,
				p.pictures, p.forms
			FROM VidalDrugBundle:Product p
			INNER JOIN p.document_all d
			LEFT JOIN p.document_merge dm
			LEFT JOIN p.parent pp
		    LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND d.ArticleID NOT IN (1,4,8)
			ORDER BY p.RusName ASC, p.ProductID ASC
		")->getResult();

        $grouped = array();

        foreach ($pds as $p) {
            $key = $p['DocumentID'] . '';

            if (!isset($grouped[$key])) {
                $grouped[$key] = array();
            }

            $grouped[$key][] = $p;
        }

        return $grouped;
    }

    public function findByDocumentID($DocumentID)
    {
        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.forms
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE d = :DocumentID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('DocumentID', $DocumentID)
            ->getResult();
    }

    public function findByPortfolio($portfolio)
    {
        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.forms
			FROM VidalDrugBundle:Product p
			JOIN p.document d
			LEFT JOIN p.document_merge dm
			JOIN d.portfolios portfolio WITH portfolio.id = :portfolioId
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('portfolioId', $portfolio->getId())
            ->getResult();
    }

    public function findProducIDDocumentID()
    {
        $raw = $this->_em->createQuery("
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.photo, p.hidePhoto, p.url, p.uri,
				p.RusName, p.RusName2, p.EngName, p.Name, p.NonPrescriptionDrug, d.ArticleID, d.Indication, d.DocumentID, 
				p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms, parent.ProductID ParentID
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN p.parent parent
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
			    AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.ProductID ASC
		")
            ->getResult();

        return $raw;
    }

    public function findForExport()
    {
        $raw = $this->_em->createQuery("
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.photo, p.hidePhoto, p.url, p.uri,
				p.RusName, p.RusName2, p.EngName, p.Name, p.NonPrescriptionDrug, d.ArticleID, d.Indication, d.DocumentID, 
				p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms, parent.ProductID ParentID
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN p.parent parent
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
			ORDER BY p.ProductID DESC
		")
            ->getResult();

        return $raw;
    }

    public function findByProductIds($productIds)
    {
        return $this->_em->createQuery("
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.photo, p.hidePhoto, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, d.ArticleID, d.Indication, d.DocumentID, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.forms
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.ProductID IN (:productIds)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('productIds', $productIds)
            ->getResult();
    }

    public function findIdsByProductIds($productIds)
    {
        return $this->_em->createQuery("
			SELECT p.ProductID
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.ProductID IN (:productIds)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('productIds', $productIds)
            ->getResult();
    }

    public function findByDocumentIDs($documentIds)
    {
        $raw = $this->_em->createQuery('
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.photo, 
			    p.hidePhoto, p.url, p.uri, p.ProductTypeCode,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, d.ArticleID, d.Indication, d.DocumentID, 
				p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.forms
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE d IN (:DocumentIDs)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('DocumentIDs', $documentIds)
            ->getResult();

        $products = array();

        foreach ($raw as $product) {
            $key = $product['ProductID'];
            if (!isset($products[$key])) {
                $products[$key] = $product;
            }
        }

        return array_values($products);
    }

    public function findByMolecules($molecules)
    {
        $moleculeIds = array();
        foreach ($molecules as $molecule) {
            $moleculeIds[] = $molecule->getMoleculeID();
        }

        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatusID, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.forms
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.moleculeNames mn
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:Molecule m WITH m = mn.MoleculeID
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE m IN (:moleculeIds)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('moleculeIds', $moleculeIds)
            ->getResult();
    }

    public function findByATCCode($ATCCode)
    {
        return $this->_em->createQuery('
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.NonPrescriptionDrug, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName
			FROM VidalDrugBundle:Product p
			JOIN p.atcCodes a WITH a = :ATCCode
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('ATCCode', $ATCCode)
            ->getResult();
    }

    public function findAllByATCCode($atcCodes)
    {
        return $this->_em->createQuery("
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.NonPrescriptionDrug, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName
			FROM VidalDrugBundle:Product p
			JOIN p.atcCodes a WITH a IN (:atcCodes)
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('atcCodes', $atcCodes)
            ->getResult();
    }

    public function findByArticle($articleId, $isDoctor = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.NonPrescriptionDrug, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName')
            ->from('VidalDrugBundle:Product', 'p')
            ->join('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->join('d.nozologies', 'n')
            ->join('n.articles', 'a')
            ->where('p.MarketStatusID IN (1,2,7)')
            ->andWhere('p.ProductTypeCode NOT IN (\'SUBS\')')
            ->andWhere('p.inactive = FALSE')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->andWhere('a.id = :articleId')
            ->andWhere('d.ArticleID IN (2,5)')
            ->setParameter('articleId', $articleId)
            ->orderBy('p.RusName', 'ASC');

        if (!$isDoctor) {
            $qb->andWhere('p.NonPrescriptionDrug = TRUE');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByNosologies($codes)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.NonPrescriptionDrug, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName')
            ->from('VidalDrugBundle:Product', 'p')
            ->join('p.document', 'd')
            ->join('d.nozologies', 'n')
            ->leftJoin('p.document_merge', 'dm')
            ->where('(n.NozologyCode IN (:codes) OR n.Code IN (:codes))')
            ->andWhere('p.MarketStatusID IN (1,2,7)')
            ->andWhere("p.ProductTypeCode NOT IN ('SUBS')")
            ->andWhere('p.inactive = FALSE')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->setParameter('codes', $codes)
            ->groupBy('p.ProductID')
            ->orderBy('p.RusName', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findByClPhGroupID($ClPhGroupsID)
    {
        return $this->_em->createQuery('
			SELECT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.NonPrescriptionDrug, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName
			FROM VidalDrugBundle:Product p
			JOIN p.clphGroups g
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE g = :ClPhGroupsID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('ClPhGroupsID', $ClPhGroupsID)
            ->getResult();
    }

    public function hasMoleculeID($MoleculeID)
    {
        $res = $this->_em->createQuery('
			SELECT count(p) as cnt
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.moleculeNames mn
			LEFT JOIN p.document d
			WHERE mn.MoleculeID = :MoleculeID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY d.ArticleID ASC
		')->setParameter('MoleculeID', $MoleculeID)
            ->getOneOrNullResult();

        return (int)$res['cnt'];
    }

    public function findByMoleculeID($MoleculeID)
    {
        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.ProductID, p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.url, p.uri,
				p.RegistrationNumber, p.RegistrationDate, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.moleculeNames mn
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE mn.MoleculeID = :MoleculeID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY d.ArticleID ASC
		')->setParameter('MoleculeID', $MoleculeID)
            ->getResult();
    }

    public function findByCompanyID($CompanyID)
    {
        $productsRaw = $this->_em->createQuery("
			SELECT p.ProductID, p.RusName, p.EngName, p.ProductTypeCode, p.Name, p.NonPrescriptionDrug, p.ZipInfo, p.url, p.uri,
				p.RegistrationNumber, p.RegistrationDate, p.photo, p.hidePhoto, p.ProductTypeCode, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				country.RusName CompanyCountry,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName, d.YearEdition, p.RusName2 RusName2,
				i.InfoPageID, i.RusName InfoPageName, co.RusName InfoPageCountry
			FROM VidalDrugBundle:Product p
			JOIN VidalDrugBundle:ProductCompany pc WITH pc.ProductID = p
			JOIN VidalDrugBundle:Company c WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN d.infoPages i
			LEFT JOIN VidalDrugBundle:Country co WITH i.CountryCode = co
			WHERE c = :CompanyID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('CompanyID', $CompanyID)
            ->getResult();

        # надо отсеять дубли препаратов
        $products = array();

        foreach ($productsRaw as $product) {
            $key = $product['ProductID'];

            if (!isset($products[$key])) {
                $products[$key] = $product;
            }
        }

        return array_values($products);
    }

    public function findByDistributor($CompanyID)
    {
        $productsRaw = $this->_em->createQuery("
			SELECT p.ProductID, p.RusName, p.EngName, p.ProductTypeCode, p.Name, p.NonPrescriptionDrug, p.ZipInfo, p.url, p.uri,
				p.RegistrationNumber, p.RegistrationDate, p.photo, p.hidePhoto, p.ProductTypeCode, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				country.RusName CompanyCountry,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName, d.YearEdition, p.RusName2 RusName2,
				i.InfoPageID, i.RusName InfoPageName, co.RusName InfoPageCountry
			FROM VidalDrugBundle:Product p
			JOIN VidalDrugBundle:ProductCompany pc WITH pc.ProductID = p
			JOIN VidalDrugBundle:Company c WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN d.infoPages i
			LEFT JOIN VidalDrugBundle:Country co WITH i.CountryCode = co
			WHERE c = :CompanyID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
				AND (pc.ItsMainCompany = 1 OR pc.CompanyRusNote LIKE 'произведено%')
			ORDER BY p.RusName ASC
		")->setParameter('CompanyID', $CompanyID)
            ->getResult();

        # надо отсеять дубли препаратов
        $products = array();

        foreach ($productsRaw as $product) {
            $key = $product['ProductID'];

            if (!isset($products[$key])) {
                $products[$key] = $product;
            }
        }

        return array_values($products);
    }

    public function findByOwner($CompanyID)
    {
        $productsRaw = $this->_em->createQuery('
			SELECT p.ProductID, p.RusName, p.EngName, p.ProductTypeCode, p.Name, p.NonPrescriptionDrug, p.ZipInfo, p.url, p.uri,
				p.RegistrationNumber, p.RegistrationDate, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				country.RusName CompanyCountry,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName, d.YearEdition, p.RusName2 RusName2,
				i.InfoPageID, i.RusName InfoPageName, co.RusName InfoPageCountry
			FROM VidalDrugBundle:Product p
			JOIN VidalDrugBundle:ProductCompany pc WITH pc.ProductID = p
			JOIN VidalDrugBundle:Company c WITH pc.CompanyID = c
			LEFT JOIN VidalDrugBundle:Country country WITH c.CountryCode = country
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN d.infoPages i
			LEFT JOIN VidalDrugBundle:Country co WITH i.CountryCode = co
			WHERE c = :CompanyID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('CompanyID', $CompanyID)
            ->getResult();

        # надо отсеять дубли препаратов
        $products = array();

        foreach ($productsRaw as $product) {
            $key = $product['ProductID'];

            if (!isset($products[$key])) {
                $products[$key] = $product;
            }
        }

        return array_values($products);
    }

    public function findByInfoPageID($InfoPageID)
    {
        $products = array();

        $byDocument = $this->_em->createQuery("
			SELECT p.ZipInfo, p.ProductID, p.RusName, p.RusName2, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo,
			    p.hidePhoto, p.url, p.uri, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				p.RegistrationNumber, p.ProductTypeCode, p.RegistrationDate, d.DocumentID, d.ArticleID, d.YearEdition
			FROM VidalDrugBundle:Product p
			JOIN p.document d
			JOIN d.infoPages i WITH i = :InfoPageID
			LEFT JOIN p.document_merge dm
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('InfoPageID', $InfoPageID)
            ->getResult();

        $byProduct = $this->_em->createQuery("
			SELECT p.ZipInfo, p.ProductID, p.RusName, p.RusName2, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo,
			    p.hidePhoto, p.url, p.uri, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				p.RegistrationNumber, p.ProductTypeCode, p.RegistrationDate, d.DocumentID, d.ArticleID, d.YearEdition
			FROM VidalDrugBundle:Product p
			JOIN p.infoPages i WITH i = :InfoPageID
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->setParameter('InfoPageID', $InfoPageID)
            ->getResult();

        if (!empty($byProduct)) {
            foreach ($byProduct as $p) {
                $key = $p['ProductID'];
                $products[$key] = $p;
            }
        }

        if (!empty($byDocument)) {
            foreach ($byDocument as $p) {
                $key = $p['ProductID'];
                $products[$key] = $p;
            }
        }

        $values = empty($products) ? array() : array_values($products);

        usort($values, function ($a, $b) {
            if ($a['RusName2'] == $b['RusName2']) {
                return 0;
            }
            return ($a['RusName2'] < $b['RusName2']) ? -1 : 1;
        });

        return $values;
    }

    public function findAutocomplete()
    {
        $products = $this->_em->createQuery("
			SELECT DISTINCT p.RusName2 AS RusName, p.EngName
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		")->getResult();

        $productNames = array();

        for ($i = 0; $i < count($products); $i++) {
            $patterns = array('/<SUP>.*<\/SUP>/iu', '/<SUB>.*<\/SUB>/iu', '/&alpha;?/', '/&plusmn;?/', '/&reg;?/', '/&shy;?/');
            $replacements = array('', '', ' ', ' ', ' ', ' ');
            $RusName = preg_replace($patterns, $replacements, $products[$i]['RusName']);
            $RusName = str_replace('"', '', $RusName);
            $RusName = mb_strtolower(str_replace('  ', ' ', $RusName), 'UTF-8');
            $RusName = preg_replace('/\\s*\\([^()]*\\)\\s*/', '', $RusName);
            $EngName = preg_replace($patterns, $replacements, $products[$i]['EngName']);
            $EngName = str_replace('"', '', $EngName);
            $EngName = mb_strtolower(str_replace('  ', ' ', $EngName), 'UTF-8');
            $EngName = preg_replace('/\\s*\\([^()]*\\)\\s*/', '', $EngName);

            if (!empty($RusName)) {
                $productNames[] = $RusName;
            }

            if (!empty($EngName)) {
                $productNames[] = $EngName;
            }
        }

        $productNames = array_unique($productNames);
        usort($productNames, 'strcasecmp');

        return $productNames;
    }

    public function findByQuery($q, $badIncluded = false)
    {
        $qb = $this->_em->createQueryBuilder();
        $anyOfWord = null;

        $qb->select('p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, p.ProductID, p.photo, p.hidePhoto, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, pt.ProductTypeCode,
				p.shortZipInfo, p.hasChildren, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.ArticleID, d.DocumentID')
            ->from('VidalDrugBundle:Product', 'p')
            ->leftJoin('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->leftJoin('VidalDrugBundle:ProductType', 'pt', 'WITH', 'p.ProductTypeCode = pt.ProductTypeCode')
            ->orderBy('p.RusName', 'ASC')
            ->andWhere('p.MarketStatusID IN (1,2,7)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
            ->andWhere('p.inactive = FALSE');;

        # включать ли бады или медицинские изделия
        if ($badIncluded) {
            $qb->andWhere("p.ProductTypeCode NOT IN ('SUBS')");
        }
        else {
            $qb->andWhere("p.ProductTypeCode NOT IN ('BAD', 'SUBS', 'MI', 'NUTR')");
        }

        $words = explode(' ', $q);

        # поиск по всем словам вместе
        $where = '';

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' AND ';
            }
            $where .= "(p.RusName2 LIKE '$word%' OR p.EngName LIKE '$word%' OR p.RusName2 LIKE '% $word%' OR p.EngName LIKE '% $word%' OR p.RusName2 LIKE '%-$word' OR p.EngName LIKE '%-$word')";
        }

        $qb->andWhere($where);
        if ($badIncluded) {
            $qb->andWhere("p.ProductTypeCode NOT IN ('SUBS')");
        }
        else {
            $qb->andWhere("p.ProductTypeCode NOT IN ('BAD', 'SUBS', 'MI', 'NUTR')");
        }
        $productsRaw = $qb->getQuery()->getResult();

        # поиск по любому из слов, если по всем не дал результата
        if (empty($productsRaw)) {
            # определяем, стоит ли искать по любому слову, должно быть хотя бы одно слово от 3х символов
            for ($i = 0; $i < count($words); $i++) {
                if (mb_strlen($words[$i], 'utf-8') > 2) {
                    $anyOfWord[] = $words[$i];
                }
            }

            if (empty($anyOfWord)) {
                return array(array(), null);
            }

            $where = '';

            for ($i = 0; $i < count($anyOfWord); $i++) {
                $word = $anyOfWord[$i];
                if ($i > 0) {
                    $where .= ' OR ';
                }
                $where .= "(p.RusName2 LIKE '$word%' OR p.EngName LIKE '$word%' OR p.RusName2 LIKE '% $word%' OR p.EngName LIKE '% $word%' OR p.RusName2 LIKE '%-$word' OR p.EngName LIKE '%-$word')";
            }

            $productsRaw = $qb
                ->andWhere('p.MarketStatusID IN (1,2,7)')
                ->andWhere('p.IsNotForSite = FALSE')
                ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
                ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
                ->andWhere('p.inactive = FALSE')
                ->andWhere('p.parent IS NULL')
                ->andWhere('p.MainID IS NULL')
                ->andWhere("p.ProductTypeCode NOT IN ('SUBS')")
                ->andWhere($where)
                ->getQuery()->getResult();
        }

        # поиск по любому из слов двухсторонний, если по всем не дал результата
        if (empty($productsRaw)) {
            # определяем, стоит ли искать по любому слову, должно быть хотя бы одно слово от 3х символов
            for ($i = 0; $i < count($words); $i++) {
                if (mb_strlen($words[$i], 'utf-8') > 2) {
                    $anyOfWord[] = $words[$i];
                }
            }

            if (empty($anyOfWord)) {
                return array(array(), null);
            }

            $where = "(p.RusName2 LIKE '%$anyOfWord[0]%' OR p.EngName LIKE '%$anyOfWord[0]%')";

            $productsRaw = $qb
                ->where('p.MarketStatusID IN (1,2,7)')
                ->andWhere('p.IsNotForSite = FALSE')
                ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
                ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
                ->andWhere('p.inactive = FALSE')
                ->andWhere('p.parent IS NULL')
                ->andWhere('p.MainID IS NULL')
                ->andWhere("p.ProductTypeCode NOT IN ('SUBS')")
                ->andWhere($where)
                ->getQuery()
                ->getResult();

            if (!empty($productsRaw)) {
                $anyOfWord = array();
            }
        }

        $products = array();
        $articlePriority = array(2, 5, 4, 3, 1);

        # отсеиваем дубли препаратов
        for ($i = 0; $i < count($productsRaw); $i++) {
            $key = $productsRaw[$i]['ProductID'];
            if (!isset($products[$key])) {
                $products[$key] = $productsRaw[$i];
            }
            else {
                # надо взять препарат по приоритету Document.ArticleID [2,5,4,3,1]
                $curr = array_search($products[$key]['ArticleID'], $articlePriority);
                $new = array_search($productsRaw[$i]['ArticleID'], $articlePriority);
                if ($new < $curr) {
                    $products[$key] = $productsRaw[$i];
                }
            }
        }

        if (is_array($anyOfWord)) {
            $anyOfWord = array_unique($anyOfWord);
        }

        return array(array_values($products), $anyOfWord);
    }

    public function findByDocuments25($documents)
    {
        $documentIds = array();

        foreach ($documents as $document) {
            if ($document['CountryEditionCode'] == 'RUS' &&
                ($document['ArticleID'] == 2 || $document['ArticleID'] == 5)
            ) {
                $documentIds[] = $document['DocumentID'];
            }
        }

        if (empty($documentIds)) {
            return array();
        }

        $productsRaw = $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, d.Indication, d.DocumentID, p.photo, p.hidePhoto,
				p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE d IN (:documentIds)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('documentIds', $documentIds)
            ->getResult();

        # исключаем повторения продуктов по приоритему
        $products = array();

        for ($i = 0, $c = count($productsRaw); $i < $c; $i++) {
            $key = $productsRaw[$i]['ProductID'];

            if (!isset($products[$key])) {
                $products[$key] = $productsRaw[$i];
            }
        }

        return $products;
    }

    public function findByDocumentPriority()
    {
        $pdo = $this->_em->getConnection();

        $stmt = "
			SELECT p.ProductID, p.RusName2 RusName2, p.RusName RusName, p.url, p.uri, p.Name, d.ArticleID, d.DocumentID, p.hasChildrenParentID, 
			    p.hasChildrenMainID, p.pictures, p.formsGrouped, p.forms, p.countPictures CountPictures, p.photo,
			    d.RusName docRusName, d.EngName docEngName, p.ga_pageviews
			FROM product p
			LEFT JOIN document d ON d.DocumentID = p.document_id
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN ('SUBS')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent_id IS NULL
				AND p.MainID IS NULL
			ORDER BY CHAR_LENGTH(p.RusName2) DESC, p.ga_pageviews DESC
		";

        $stmt = $pdo->prepare($stmt);
        $stmt->execute();
        $productsRaw = $stmt->fetchAll();

        $products = array();
        foreach ($productsRaw as $p) {
            $key = mb_strtolower($p['RusName'], 'utf-8');
            if (!isset($products[$key])) {
                $products[$key] = $p;
            }
            $key2 = mb_strtolower($p['RusName2'], 'utf-8');
            if (!isset($products[$key2])) {
                $products[$key2] = $p;
            }
        }

        return $products;
    }

    public function findByDocuments4($documents)
    {
        $documentIds = array();

        foreach ($documents as $document) {
            if ($document['CountryEditionCode'] == 'RUS' &&
                $document['ArticleID'] == 4
            ) {
                $documentIds[] = $document['DocumentID'];
            }
        }

        if (empty($documentIds)) {
            return array();
        }

        $productsRaw = $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.ProductID, p.photo, p.hidePhoto, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, d.Indication, d.DocumentID, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE d IN (:documentIds)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('documentIds', $documentIds)
            ->getResult();

        # исключаем повторения продуктов по приоритему
        $products = array();

        for ($i = 0, $c = count($productsRaw); $i < $c; $i++) {
            $key = $productsRaw[$i]['ProductID'];

            if (!isset($products[$key])) {
                $products[$key] = $productsRaw[$i];
            }
        }

        return $products;
    }

    public function findByClPhGroup($description)
    {
        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ClPhGrDescription
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE d.ClPhGrName = :description
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('description', $description)
            ->getResult();
    }

    public function findByPhThGroup($id)
    {
        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.RegistrationNumber, p.RegistrationDate, ms.RusName MarketStatus, p.ProductID, p.url, p.uri,
				p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID
			FROM VidalDrugBundle:Product p
			JOIN p.phthgroups g WITH g.id = :id
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND (d.IsApproved IS NULL OR d.IsApproved = TRUE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('id', $id)
            ->getResult();
    }

    public function findPhThGroupsByQuery($q)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('DISTINCT g.Name, g.id')
            ->from('VidalDrugBundle:Product', 'p')
            ->join('p.phthgroups', 'g')
            ->leftJoin('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->where("p.MarketStatusID IN (1,2,7) AND p.ProductTypeCode NOT IN ('SUBS') AND p.inactive = FALSE AND (d.inactive IS NULL OR d.inactive = FALSE) AND (d.IsApproved IS NULL OR d.IsApproved = TRUE) AND p.IsNotForSite = FALSE AND p.parent IS NULL AND p.MainID IS NULL")
            ->orderBy('g.Name', 'ASC');

        # поиск по всем словам словам
        $where = '';
        $words = explode(' ', $q);

        for ($i = 0; $i < count($words); $i++) {
            $word = $words[$i];
            if ($i > 0) {
                $where .= ' OR ';
            }
            $where .= "(g.Name LIKE '$word%' OR g.Name LIKE '% $word%')";
        }

        $qb->andWhere($where);
        $groups = $qb->getQuery()->getResult();

        # поиск по любому из слов
        if (empty($groups)) {
            foreach ($words as $word) {
                if (mb_strlen($word, 'utf-8') < 4) {
                    return array();
                }
            }

            $where = '';

            for ($i = 0; $i < count($words); $i++) {
                $word = $words[$i];
                if ($i > 0) {
                    $where .= ' AND ';
                }
                $where .= "(g.Name LIKE '$word%' OR g.Name LIKE '% $word%')";
            }

            $qb->where("p.MarketStatusID IN (1,2,7) AND p.ProductTypeCode NOT IN ('SUBS') AND p.inactive = FALSE AND (d.inactive IS NULL OR d.inactive = FALSE) AND (d.IsApproved IS NULL OR d.IsApproved = TRUE) AND p.IsNotForSite = FALSE AND p.parent IS NULL AND p.MainID IS NULL");
            $qb->andWhere($where);
            $groups = $qb->getQuery()->getResult();
        }

        for ($i = 0, $c = count($groups); $i < $c; $i++) {
            $name = $this->mb_ucfirst($groups[$i]['Name']);
            $groups[$i]['Name'] = preg_replace('/' . $q . '/iu', '<span class="query">$0</span>', $name);
        }

        return $groups;
    }

    public function getQueryByLetter($letter, $type, $nonPrescription)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('DISTINCT p.ProductID, p.ZipInfo, p.RegistrationNumber, p.RegistrationDate,
                p.url, p.uri, p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.photo, p.hidePhoto, p.hasChildrenParentID,
                p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ClPhGrDescription')
            ->from('VidalDrugBundle:Product', 'p')
            ->leftJoin('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->andWhere('p.MarketStatusID IN (1,2,7)')
            ->andWhere('p.inactive = FALSE')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->orderBy('p.RusName', 'ASC');

        if ($letter) {
            $qb->andWhere('p.RusName LIKE :likeName')->setParameter('likeName', $letter . '%');
        }

        if ($type == 'p') {
            $qb->andWhere("p.ProductTypeCode NOT IN ('SUBS', 'BAD', 'MI')");
        }
        elseif ($type == 'b') {
            $qb->andWhere("p.ProductTypeCode IN ('BAD', 'MI')");
        }
        elseif ($type == 'n') {
            $qb->andWhere("p.ProductTypeCode IN ('NUTR')");
        }
        else {
            $qb->andWhere("p.ProductTypeCode NOT IN ('SUBS')");
        }

        if ($nonPrescription) {
            $qb->andWhere('p.NonPrescriptionDrug = 1');
        }

        return $qb->getQuery();
    }

    public function getProductsByLetter($type, $nonPrescription)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('DISTINCT p')
            ->from('VidalDrugBundle:Product', 'p')
            ->leftJoin('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->andWhere('p.MarketStatusID IN (1,2,7)')
            ->andWhere('p.inactive = FALSE')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere('(d.IsApproved IS NULL OR d.IsApproved = TRUE)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->orderBy('p.RusName2', 'ASC');

        if ($type == 'p') {
            $qb->andWhere("p.ProductTypeCode NOT IN ('SUBS', 'BAD', 'MI')");
        }
        elseif ($type == 'b') {
            $qb->andWhere("p.ProductTypeCode IN ('BAD', 'MI')");
        }
        elseif ($type == 'n') {
            $qb->andWhere("p.ProductTypeCode IN ('NUTR')");
        }
        else {
            $qb->andWhere("p.ProductTypeCode NOT IN ('SUBS')");
        }

        if ($type != 'n' && $nonPrescription) {
            $qb->andWhere('p.NonPrescriptionDrug = 1');
        }

        return $qb->getQuery()->getResult();
    }

    public function findMarketStatusesByProductIds($productIds)
    {
        $raw = $this->_em->createQuery('
			SELECT p.ProductID, ms.RusName MarketStatus
			FROM VidalDrugBundle:Product p
			JOIN p.MarketStatusID ms
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			WHERE p.ProductID IN (:productIds)
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND p.IsNotForSite = FALSE
		')->setParameter('productIds', $productIds)
            ->getResult();

        $marketStatuses = array();

        for ($i = 0; $i < count($raw); $i++) {
            $key = $raw[$i]['ProductID'];
            $marketStatuses[] = $raw[$i]['MarketStatus'];
        }

        return $marketStatuses;
    }

    public function findByKfu($ClPhPointerID)
    {
        return $this->_em->createQuery('
			SELECT p.ZipInfo, p.ProductID, p.RusName, p.EngName, p.Name, p.NonPrescriptionDrug, p.url, p.uri,
				p.RegistrationNumber, p.RegistrationDate, p.photo, p.hidePhoto, p.hasChildrenParentID, p.hasChildrenMainID, dm.RusName docRusName, dm.Elaboration docElaboration, p.multiForm, dm.EngName docEngName, p.pictures, p.formsGrouped, p.forms,
				d.Indication, d.DocumentID, d.ArticleID, d.RusName DocumentRusName, d.EngName DocumentEngName,
				d.Name DocumentName
			FROM VidalDrugBundle:Product p
			JOIN p.document d
			LEFT JOIN p.document_merge dm
			JOIN d.clphPointers pointer
			WHERE pointer = :id
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
				AND d.ArticleID IN (1,2,3,4,5)
			ORDER BY p.RusName ASC
		')->setParameter('id', $ClPhPointerID)
            ->getResult();
    }

    public function findAllNames()
    {
        return $this->_em->createQuery('
			SELECT DISTINCT p.RusName
			FROM VidalDrugBundle:Product p
			ORDER BY p.RusName
		')->getResult();
    }

    public function countByCompanyID($CompanyID)
    {
        return $this->_em->createQuery('
			SELECT COUNT(DISTINCT p.ProductID)
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			JOIN VidalDrugBundle:ProductCompany pc WITH pc.ProductID = p
			JOIN VidalDrugBundle:Company c WITH pc.CompanyID = c
			WHERE c = :CompanyID
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('CompanyID', $CompanyID)
            ->getSingleScalarResult();
    }

    public function countByDocumentIds($documentIds)
    {
        if (empty($documentIds)) {
            return 0;
        }

        $count = $this->_em->createQuery('
			SELECT COUNT(DISTINCT p.ProductID)
			FROM VidalDrugBundle:Product p
			LEFT JOIN p.document d
			LEFT JOIN p.document_merge dm
			LEFT JOIN VidalDrugBundle:MarketStatus ms WITH ms.MarketStatusID = p.MarketStatusID
			WHERE d IN (:DocumentIDs)
				AND p.MarketStatusID IN (1,2,7)
				AND p.ProductTypeCode NOT IN (\'SUBS\')
				AND p.inactive = FALSE
				AND (d.inactive IS NULL OR d.inactive = FALSE)
				AND p.IsNotForSite = FALSE
				AND p.parent IS NULL
				AND p.MainID IS NULL
			ORDER BY p.RusName ASC
		')->setParameter('DocumentIDs', $documentIds)
            ->getSingleScalarResult();

        return $count;
    }

    public function findByProductType($t = 'p', $n = false)
    {
        $pdo = $this->_em->getConnection();

        switch ($t) {
            case 'p':
                $where = "('DRUG', 'GOME', 'ALRG', 'DIAG', 'SRED')";
                break;
            case 'b':
                $where = "('BAD')";
                break;
            default:
                $where = "('DRUG', 'GOME', 'ALRG', 'DIAG', 'SRED', 'BAD')";
        }

        if ($n) {
            $where .= " AND NonPrescriptionDrug = 1";
        }

        $sql = "
			SELECT DISTINCT LEFT(RusName , 2) as letters
			FROM product
			WHERE LEFT(RusName, 1) NOT IN ('1','2','3','5','9','_','D','H','L','N','Q','S')
				AND MarketStatusID IN (1,2,7)
				AND ProductTypeCode IN {$where}
			ORDER BY letters
		";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $raw = $stmt->fetchAll();
        $syllables = array();
        $secondLetters = array();

        foreach ($raw as $r) {
            $first = mb_substr($r['letters'], 0, 1, 'utf-8');
            $second = mb_substr($r['letters'], 1, 2, 'utf-8');

            isset($syllables[$first])
                ? $syllables[$first][] = $r['letters']
                : $syllables[$first] = array($r['letters']);

            if (!isset($secondLetters[$second])) {
                $secondLetters[$second] = true;
            }
        }

        $raws = array();
        $table = array();
        $firstLetters = array_keys($syllables);
        $secondLetters = array_keys($secondLetters);

        usort($secondLetters, 'strcmp');

        foreach ($raw as $r) {
            $key = $r['letters'];
            $raws[$key] = true;
        }

        foreach ($secondLetters as $secondLetter) {
            $table[$secondLetter] = array();

            foreach ($firstLetters as $firstLetter) {
                $key = $firstLetter . $secondLetter;
                $table[$secondLetter][] = isset($raws[$key]) ? $key : null;
            }
        }

        return array($syllables, $table);
    }

    public function findVidalboxProducts()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from('VidalDrugBundle:Product', 'p')
            ->join('p.ads', 'a')
            ->leftJoin('p.document', 'd')
            ->leftJoin('p.document_merge', 'dm')
            ->where('a.enabled = TRUE')
            ->andWhere('p.MarketStatusID IN (1,2,7)')
            ->andWhere('p.IsNotForSite = FALSE')
            ->andWhere('p.parent IS NULL')
            ->andWhere('p.MainID IS NULL')
            ->andWhere('p.inactive = FALSE')
            ->andWhere('(d.inactive IS NULL OR d.inactive = FALSE)')
            ->andWhere("p.ProductTypeCode NOT IN ('SUBS')")
            ->orderBy('p.RusName2', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function publicationsByProduct($ProductID)
    {
        return $this->_em->createQuery('
			SELECT p.id, p.title, p.date, product.RusName productTitle
			FROM VidalDrugBundle:Publication p
			JOIN p.products product WITH product.ProductID = :ProductID
			WHERE p.enabled = TRUE
			ORDER BY p.date DESC
		')->setParameter('ProductID', $ProductID)
            ->getResult();
    }

    public function findAllATC($product)
    {
        foreach ($product->getATCCodes() as $atcCode) {
            $this->findParentATC($atcCode);
        }

        return $this->atcCodes;
    }

    public function publicationsByAtc($atcCodes)
    {
        $raw = $this->_em->createQuery('
			SELECT p.id, p.title, p.date, atc.ATCCode atcTitle
			FROM VidalDrugBundle:Publication p
			JOIN p.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
			WHERE p.enabled = TRUE
			ORDER BY p.date DESC
		')->setParameter('atcCodes', $atcCodes)
            ->getResult();

        $results = array();

        foreach ($raw as $r) {
            $key = $r['atcTitle'];

            isset ($results[$key])
                ? $results[$key][] = $r
                : $results[$key] = array($r);
        }

        return $results;
    }

    public function publicationsByMolecule($ProductID)
    {
        $raw = $this->_em->createQuery('
			SELECT p.id, p.title, p.date, m.RusName moleculeTitle
			FROM VidalDrugBundle:Publication p
			JOIN p.molecules m
			JOIN m.moleculeNames mn
			JOIN mn.products product WITH product.ProductID = :ProductID
			WHERE p.enabled = TRUE
				AND SIZE(product.moleculeNames) = 1
			ORDER BY p.date DESC
		')->setParameter('ProductID', $ProductID)
            ->getResult();

        $results = array();

        foreach ($raw as $r) {
            $key = $r['moleculeTitle'];

            isset ($results[$key])
                ? $results[$key][] = $r
                : $results[$key] = array($r);
        }

        return $results;
    }

    public function articlesByProduct($ProductID)
    {
        return $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.rubrique rubrique, r.title rubriqueTitle, product.RusName productTitle
			FROM VidalDrugBundle:Article a
			JOIN a.products product WITH product.ProductID = :ProductID
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY a.date DESC
		')->setParameter('ProductID', $ProductID)
            ->getResult();
    }

    public function articlesByAtc($atcCodes)
    {
        $raw = $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.rubrique rubrique, r.title rubriqueTitle, atc.ATCCode atcTitle
			FROM VidalDrugBundle:Article a
			JOIN a.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY a.date DESC
		')->setParameter('atcCodes', $atcCodes)
            ->getResult();

        $results = array();

        foreach ($raw as $r) {
            $key = $r['atcTitle'];

            isset ($results[$key])
                ? $results[$key][] = $r
                : $results[$key] = array($r);
        }

        return $results;
    }

    public function articlesByMolecule($ProductID)
    {
        $raw = $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.rubrique rubrique, r.title rubriqueTitle, m.RusName moleculeTitle
			FROM VidalDrugBundle:Article a
			JOIN a.molecules m
			JOIN m.moleculeNames mn
			JOIN mn.products product WITH product.ProductID = :ProductID
			JOIN a.rubrique r
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
			ORDER BY a.date DESC
		')->setParameter('ProductID', $ProductID)
            ->getResult();

        $results = array();

        foreach ($raw as $r) {
            $key = $r['moleculeTitle'];

            isset ($results[$key])
                ? $results[$key][] = $r
                : $results[$key] = array($r);
        }

        return $results;
    }

    public function artsByProduct($ProductID)
    {
        if (empty($ProductID)) {
            return array();
        }

        return $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.url rubriqueUrl, r.title rubriqueTitle,
				c.url categoryUrl, t.url typeUrl, product.RusName productTitle
			FROM VidalDrugBundle:Art a
			JOIN a.products product WITH product.ProductID = :ProductID
			JOIN a.rubrique r
			LEFT JOIN a.category c
			LEFT JOIN a.type t
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
				AND (t.id IS NULL OR t.enabled = TRUE)
				AND (c.id IS NULL OR c.enabled = TRUE)
			ORDER BY a.date DESC
		')->setParameter('ProductID', $ProductID)
            ->getResult();
    }

    public function artsByAtc($atcCodes)
    {
        if (empty($atcCodes)) {
            return array();
        }

        $raw = $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.url rubriqueUrl, r.title rubriqueTitle,
				c.url categoryUrl, t.url typeUrl, atc.ATCCode atcTitle
			FROM VidalDrugBundle:Art a
			JOIN a.atcCodes atc WITH atc.ATCCode IN (:atcCodes)
			JOIN a.rubrique r
			LEFT JOIN a.category c
			LEFT JOIN a.type t
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
				AND (t.id IS NULL OR t.enabled = TRUE)
				AND (c.id IS NULL OR c.enabled = TRUE)
			ORDER BY a.date DESC
		')->setParameter('atcCodes', $atcCodes)
            ->getResult();

        $results = array();

        foreach ($raw as $r) {
            $key = $r['atcTitle'];

            isset ($results[$key])
                ? $results[$key][] = $r
                : $results[$key] = array($r);
        }

        return $results;
    }

    public function artsByMolecule($ProductID)
    {
        if (empty($ProductID)) {
            return array();
        }

        $raw = $this->_em->createQuery('
			SELECT a.id, a.title, a.link, r.url rubriqueUrl, r.title rubriqueTitle,
				c.url categoryUrl, t.url typeUrl, m.RusName moleculeTitle
			FROM VidalDrugBundle:Art a
			JOIN a.molecules m
			JOIN m.moleculeNames mn
			JOIN mn.products product WITH product.ProductID = :ProductID
			JOIN a.rubrique r
			LEFT JOIN a.category c
			LEFT JOIN a.type t
			WHERE a.enabled = TRUE
				AND r.enabled = TRUE
				AND (t.id IS NULL OR t.enabled = TRUE)
				AND (c.id IS NULL OR c.enabled = TRUE)
			ORDER BY a.date DESC
		')->setParameter('ProductID', $ProductID)
            ->getResult();

        $results = array();

        foreach ($raw as $r) {
            $key = $r['moleculeTitle'];

            isset ($results[$key])
                ? $results[$key][] = $r
                : $results[$key] = array($r);
        }

        return $results;
    }

    /**
     * Функция возвращает слово с заглавной первой буквой (c поддержкой кирилицы)
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    private function mb_ucfirst($string, $encoding = 'utf-8')
    {
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding) . $then;
    }

    private function findParentATC($atcCode)
    {
        $this->atcCodes[] = $atcCode->getATCCode();

        if ($parent = $atcCode->getParent()) {
            $this->findParentATC($parent);
        }
    }
}
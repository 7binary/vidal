<?php
namespace Vidal\DrugBundle\Service;

use Vidal\DrugBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductService
{
    protected $rootDir;
    protected $container;

    public function __construct(ContainerInterface $container, $rootDir)
    {
        $this->rootDir = $rootDir;
		$this->container = $container;
    }

    public function getLetters($type, $withoutRecipe)
    {
        $lettersFile = $this->rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Vidal'.
            DIRECTORY_SEPARATOR.'DrugBundle'.DIRECTORY_SEPARATOR.'Generated'.DIRECTORY_SEPARATOR.'product_letters.json';

        $productLetters = json_decode(file_get_contents($lettersFile), true);

        $lettersKey = $type . ($withoutRecipe ? '-non' : '');
        $letters = $productLetters[$lettersKey];

        return $letters;
    }

    
    /**
     * Получение аналогов у продукта
     * 
     * @access public
     * @param Product $product
     * @param int $EqRateType
     * @return array
     * Дубль Vidal\DrugBundle\Controller\ApiController equalAjaxAction 
     * TODO объеденить
     */
    public function getAnalogs(Product $product, $EqRateType)
    {
        $em  = $this->container->get('doctrine')->getManager('drug');
        $pdo = $em->getConnection();

        $result = [];
        $ProductTypeCode = $product->getProductTypeCode();
        $productMolecules = $product->getMolecules();
        $ProductID = $product->getId();

        $params = array();
        $params['EqRateType'] = $EqRateType;
        $anyProducts = array('a0' => array(), 'a1' => array(), 'a2' => array(), 'a3' => array());
        $anyProductIds = array();
        $atcs = $product->getAtcs();

        $RoaRaw = $em->createQuery("
            SELECT iroa.RouteID
            FROM VidalDrugBundle:ProductItem pri
            JOIN VidalDrugBundle:ProductItemRoute iroa
              WITH iroa.ProductID = pri.ProductID AND iroa.ItemID = pri.ItemID
            WHERE pri.ProductID = :ProductID
        ")->setParameter('ProductID', $ProductID)
            ->getResult();
        $roa = array();

        foreach ($RoaRaw as $r) {
            $roa[] = $r['RouteID'];
        }

        $roaQ = empty($roa) ? '0' : implode(',', $roa);

        # полные аналоги препарата
        if ($EqRateType == 0 || $EqRateType == 4) {
            $stmt = $pdo->prepare("
                SELECT mol.MoleculeID
                FROM product_moleculename pmn
                INNER JOIN moleculename mn ON mn.MoleculeNameID = pmn.MoleculeNameID
                INNER JOIN molecule mol ON mol.MoleculeID = mn.MoleculeID
                WHERE pmn.ProductID = $ProductID
                  AND mol.MoleculeID NOT IN (2203,1144)
            ");
            $stmt->execute();
            $midRaw = $stmt->fetchAll();
            $mid = array();

            foreach ($midRaw as $r) {
                $mid[] = $r['MoleculeID'];
            }

            $MCount = count($mid);
            if ($MCount == 0 && $EqRateType != 4) {
                return [];
            }

            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName, pr.uri, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND (
                    select COUNT(pmn.MoleculeNameID)
                    from product_moleculename pmn
                    inner join moleculename mn on mn.MoleculeNameID = pmn.MoleculeNameID
                    inner join molecule mol on mol.MoleculeID = mn.MoleculeID
                    where pmn.ProductID = pr.ProductID and mol.MoleculeID not in (2203,1144)
                  ) > 0
                  AND pr.molecules = '$productMolecules'
                  AND (
                    select COUNT(*)
                    from product_item pri
                    inner join product_item_route iroa ON iroa.ProductID = pri.ProductID and iroa.ItemID = pri.ItemID
                    where pri.ProductID = pr.ProductID and iroa.RouteID in ($roaQ)
                  ) > 0
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC  
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();

            if ($EqRateType == 4) {
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $key = $product['ProductID'];
                        if (!in_array($key, $anyProducts)) {
                            $anyProducts['a0'][$key] = $product;
                            $anyProductIds[] = $key;
                        }
                    }
                }
            } else {
                $result = array('a0' => $products);
                return $result;
            }
        }

        # ATC связи
        $atc3 = array();
        $atc4 = array();
        $atc5 = array();

        foreach ($atcs as $atc) {
            $length = strlen($atc);
            if ($length == 4) {
                $atc3[] = $atc;
            }
            elseif ($length == 5) {
                $atc4[] = $atc;
            }
            elseif ($length >= 6) {
                $atc5[] = $atc;
            }
        }

        /* --- Групповые аналоги --- */
        if ($EqRateType == 1 || $EqRateType == 4) {
            $regexp = empty($atc5) ? '---' : implode(' |', $atc5) . ' ';
            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND pr.atcs REGEXP '$regexp'
                  AND (
                    select COUNT(*)
                    from product_item pri
                    inner join product_item_route iroa ON iroa.ProductID = pri.ProductID and iroa.ItemID = pri.ItemID
                    where pri.ProductID = pr.ProductID and iroa.RouteID in ($roaQ)
                  ) > 0
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();

            if ($EqRateType == 4) {
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $key = $product['ProductID'];
                        if (!in_array($key, $anyProducts)) {
                            $anyProducts['a1'][$key] = $product;
                            $anyProductIds[] = $key;
                        }
                    }
                }
            } else {
                $result = array('a1' => $products);
                return $result;
            }
        }

        /* --- Нозологические аналоги --- */
        if ($EqRateType == 2 || $EqRateType == 4) {
            $regexp = empty($atc4) ? '---' : implode(' |', $atc4) . ' ';
            $stmt = $pdo->prepare("
                SELECT pr.ProductID, pr.RusName2, pr.Name, pr.url, pr.ZipInfo, pr.forms, 
                  co.GDDBName, cn.RusName countryName
                FROM product pr
                LEFT JOIN product_company pc ON pc.ProductID = pr.ProductID AND pc.ItsMainCompany = 1
                LEFT JOIN company co ON co.CompanyID = pc.CompanyID
                LEFT JOIN country cn ON cn.CountryCode = co.CountryCode
                WHERE pr.ProductID != $ProductID
                  AND pr.ProductTypeCode = '$ProductTypeCode'
                  AND pr.atcs REGEXP '$regexp'
                  AND pr.MarketStatusID IN (1,2,7)
                  AND pr.ProductTypeCode NOT IN ('SUBS')
                  AND pr.IsNotForSite = 0
                  AND pr.parent_id IS NULL
                  AND pr.MainID IS NULL
                ORDER BY pr.RusName2 ASC
            ");

            $stmt->execute();
            $products = $stmt->fetchAll();

            if ($EqRateType == 4) {
                if (!empty($products)) {
                    foreach ($products as $product) {
                        $key = $product['ProductID'];
                        if (!in_array($key, $anyProducts)) {
                            $anyProducts['a2'][$key] = $product;
                            $anyProductIds[] = $key;
                        }
                    }
                }
            } else {
                $result = array('a2' => $products);
                return $result;
            }
        }

        return $anyProducts;
    }

    public function getAnalogProducts($products)
    {
        $i = 1;
        $productsAll = array();
        foreach ($products as $category => $productList) {
            foreach ($productList as &$product) {
                $key = $product['ProductID'];
                if (!isset($productsAll[$key])) {
                    $product['category'] = $category;
                    $product['index'] = $i;
                    $productsAll[$key] = $product;
                    $i++;
                }
            }
        }

        $products = array_values($productsAll);

        if (empty($products)) {
            return array();
        }

        /** @var EntityManager $em */
        $em  = $this->container->get('doctrine')->getManager('drug');
        $productIds = array();

        foreach ($products as $product) {
            $productIds[] = intval($product['ProductID']);
        }

        $products = $em->getRepository(Product::class)->findByProductIds($productIds);
        if (empty($products)) {
            return array();
        }

        $productCategories = array();
        foreach ($products as &$product) {
            $key = $product['ProductID'];
            $category = $productsAll[$key]['category'];
            $product['category'] = $category;
            $product['index'] = $productsAll[$key]['index'];

            if (!isset($productCategories[$category])) {
                $productCategories[$category] = array();
            }
            $productCategories[$category][] = $product;
        }

        usort($products, function ($p1, $p2) {
            return $p1['index'] > $p2['index'];
        });
        ksort($productCategories);

        return $productCategories;
    }

}
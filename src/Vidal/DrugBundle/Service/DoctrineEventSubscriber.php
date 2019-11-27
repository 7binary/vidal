<?php

namespace Vidal\DrugBundle\Service;

use Doctrine\ORM\EntityManager;
use Elasticsearch\ClientBuilder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpKernel\KernelInterface;
use Vidal\DrugBundle\Command\AllCommand;
use Vidal\DrugBundle\Command\ArticleLinkedCommand;
use Vidal\DrugBundle\Command\ArtLinkedCommand;
use Vidal\DrugBundle\Command\AtcCountCommand;
use Vidal\DrugBundle\Command\AtcParentCommand;
use Vidal\DrugBundle\Command\GeneratorAtcCommand;
use Vidal\DrugBundle\Command\GeneratorKfuCommand;
use Vidal\DrugBundle\Command\GeneratorNozologyCommand;
use Vidal\DrugBundle\Command\KfuCountCommand;
use Vidal\DrugBundle\Command\KfuNameCommand;
use Vidal\DrugBundle\Command\KfuUrlCommand;
use Vidal\DrugBundle\Command\NozologyClassCommand;
use Vidal\DrugBundle\Command\NozologyCodeCommand;
use Vidal\DrugBundle\Command\NozologyCountCommand;
use Vidal\DrugBundle\Command\NozologyLevelCommand;
use Vidal\DrugBundle\Command\NozologyNameCommand;
use Vidal\DrugBundle\Command\PublicationLinkCommand;
use Vidal\DrugBundle\Command\PublicationLinkedCommand;
use Vidal\DrugBundle\Entity\Ads;
use Vidal\DrugBundle\Entity\AdsSlider;
use Vidal\DrugBundle\Entity\Article;
use Vidal\DrugBundle\Entity\Art;
use Vidal\DrugBundle\Entity\ATC;
use Vidal\DrugBundle\Entity\ClinicoPhPointers;
use Vidal\DrugBundle\Entity\Nozology;
use Vidal\DrugBundle\Entity\Publication;
use Vidal\DrugBundle\Entity\Product;
use Vidal\DrugBundle\Entity\Document;
use Vidal\DrugBundle\Entity\PharmPortfolio;
use Vidal\DrugBundle\Entity\Tag;
use Vidal\DrugBundle\Entity\InfoPage;
use Vidal\MainBundle\Entity\Log;
use Vidal\MainBundle\Entity\KeyValue;
use Vidal\MainBundle\Entity\User;

class DoctrineEventSubscriber implements EventSubscriber
{
    public static $monitorAdminIds = array(53084);

    /** @var ContainerInterface */
    private $container;

    private $manualMainID = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Возвращает список имён событий, которые обрабатывает данный класс. Callback-методы должны иметь такие же имена
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'postPersist',
            'preUpdate',
            'postUpdate',
            'preRemove',
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            # проставляем ссылку, если пустая
            if ($entity instanceof Article || $entity instanceof Art) {
                $this->setLink($entity);
            }

            if ($entity instanceof Publication) {
                $this->checkDuplicateTitle($args, 'publication');
            }
            elseif ($entity instanceof Article) {
                $this->checkDuplicateTitle($args, 'article');
            }
            elseif ($entity instanceof Art) {
                $this->checkDuplicateTitle($args, 'art');
            }
            elseif ($entity instanceof Document) {
                $this->checkDuplicateDocument($args);
            }
            elseif ($entity instanceof Product) {
                $this->checkDuplicateProduct($args);
                $this->setProductUrl($args);
            }
        }
        catch (\Exception $e) {
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            # проставляем мета к видео, если его загрузили
            if ($entity instanceof Article || $entity instanceof Art || $entity instanceof Publication
                || $entity instanceof PharmPortfolio || $entity instanceof Ads || $entity instanceof AdsSlider
            ) {
                $this->setVideoMeta($entity);
            }

            if ($entity instanceof Product) {
                $this->autocompleteProduct($entity);
                $this->updateCountProducts($args);
                $this->updateProductUrl($args);
                $this->updateProductNames($args);
            }

            if (AllCommand::$db_update == false && $entity instanceof ClinicoPhPointers) {
                $this->updatedKfu();
            }
            if (AllCommand::$db_update == false && $entity instanceof ATC) {
                $this->updatedAtc();
            }
            if (AllCommand::$db_update == false && $entity instanceof Nozology) {
                $this->updatedNozology();
            }

            if ($entity instanceof Publication) {
                $this->generatePublicationLink($args);
            }

            /** @var User $user */
            if ($token = $this->container->get('security.context')->getToken()) {
                if ($user = $token->getUser()) {
                    if (in_array($user->getId(), self::$monitorAdminIds)) {
                        /** @var EntityManager $em */
                        $em = $this->container->get('doctrine')->getManager('default');
                        $log = new Log();
                        $log->setAdminEmail($user->getUsername());
                        $log->setAdminId($user->getId());
                        $log->setEvent(Log::EVENT_CREATE);
                        $log->setEntityClass(get_class($entity));
                        $log->setEntityId($entity->getId());
                        $em->persist($log);
                        $em->flush($log);
                    }
                }
            }
        }
        catch (\Exception $e) {
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        try {
            $entity = $args->getEntity();
            $pdo = $args->getEntityManager()->getConnection();

            # проставляем ссылку, если пустая
            if ($entity instanceof Article || $entity instanceof Art) {
                $this->setLink($entity);
            }

            if ($entity instanceof InfoPage) {
                if ($tag = $entity->getTag()) {
                    $pdo->prepare("UPDATE tag SET InfoPageID = NULL WHERE InfoPageID = {$entity->getInfoPageID()}")->execute();
                    $pdo->prepare("UPDATE tag SET InfoPageID = {$entity->getInfoPageID()} WHERE id = {$tag->getId()}")->execute();
                }
            }

            if ($entity instanceof Tag) {
                if ($infoPage = $entity->getInfoPage()) {
                    $pdo->prepare("UPDATE infopage SET tag_id = NULL WHERE tag_id = {$entity->getId()}")->execute();
                    $pdo->prepare("UPDATE infopage SET tag_id = {$entity->getId()} WHERE InfoPageID = {$infoPage->getInfoPageID()}")->execute();
                }
            }

            if ($entity instanceof Product) {
                if ($args->hasChangedField('document_manual_id')) {
                    $pdo->prepare("UPDATE product SET document_id={$entity->getDocumentManualId()} WHERE ProductID={$entity->getProductID()}")->execute();
                }
                if ($args->hasChangedField('MainIDManual')) {
                    $this->manualMainID = true;
                }
            }

            /** @var User $user */
            if ($token = $this->container->get('security.context')->getToken()) {
                if ($user = $token->getUser()) {
                    if (in_array($user->getId(), self::$monitorAdminIds)) {
                        /** @var EntityManager $em */
                        $em = $this->container->get('doctrine')->getManager('default');
                        $emDrug = $this->container->get('doctrine')->getManager('drug');
                        $changes = array();
                        $metadata = $emDrug->getClassMetadata(get_class($entity));
                        foreach ($metadata->fieldNames as $fieldName) {
                            if ($args->hasChangedField($fieldName)) {
                                $oldValue = $args->getOldValue($fieldName);
                                $newValue = $args->getNewValue($fieldName);
                                if ($oldValue instanceof \DateTime) {
                                    $oldValue = $oldValue->format('d.m.Y H:i:s');
                                    $newValue = $newValue->format('d.m.Y H:i:s');
                                }
                                $changes[] = $fieldName . ' >>> ' . $oldValue . ' >>> ' . $newValue;
                            }
                        }
                        $changes = implode(' ||| ', $changes);

                        $log = new Log();
                        $log->setAdminEmail($user->getUsername());
                        $log->setAdminId($user->getId());
                        $log->setEvent(Log::EVENT_UPDATE);
                        $log->setEntityClass(get_class($entity));
                        $log->setEntityId($entity->getId());
                        $log->setChanges($changes);
                        $em->persist($log);
                        $em->flush($log);
                    }
                }
            }
        }
        catch (\Exception $e) {
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        try {
            $pdo = $args->getEntityManager()->getConnection();
            $entity = $args->getEntity();

            # проставляем мета к видео, если его загрузили
            if ($entity instanceof Article || $entity instanceof Art || $entity instanceof Publication
                || $entity instanceof PharmPortfolio || $entity instanceof Ads || $entity instanceof AdsSlider
            ) {
                $this->setVideoMeta($entity);
            }

            # проставляем сколько всего связей у тегов (Tag.total)
            if ($entity instanceof Article || $entity instanceof Art || $entity instanceof Publication) {
                $tagService = $this->container->get('drug.tag_total');
                foreach ($entity->getTags() as $tag) {
                    $tagService->count($tag->getId());
                }
                foreach ($entity->getInfoPages() as $ip) {
                    if ($tag = $ip->getTag()) {
                        $tagService->count($tag->getId());
                    }
                }
            }

            if ($entity instanceof Product) {
                $pdo->prepare("SET FOREIGN_KEY_CHECKS=0;")->execute();
                # необходимо выставленные вручную идентификаторы перезаписывать, дабы они никогда не сбивались
                $pdo->prepare("UPDATE product SET MainID = MainIDManual WHERE MainIDManual IS NOT NULL")->execute();
                $pdo->prepare("UPDATE product SET MainID = NULL, MainIDManual = NULL WHERE MainIDManual = 0 OR MainIDManual = '0'")->execute();
                $pdo->prepare("UPDATE product SET document_id = document_manual_id WHERE document_manual_id IS NOT NULL")->execute();
                $pdo->prepare("UPDATE product SET document_id = NULL, document_manual_id = NULL, document_merge_id = NULL WHERE document_manual_id = 0 OR document_manual_id = '0'")->execute();

                # остальные служебные обновления полей продукта
                $this->updateProductUrl($args);
                $this->updateProductNames($args);
                if ($this->manualMainID) {
                    $this->productMainID($args);
                }
            }

            if (AllCommand::$db_update == false && $entity instanceof ClinicoPhPointers) {
                $this->updatedKfu();
            }
            if (AllCommand::$db_update == false && $entity instanceof ATC) {
                $this->updatedAtc();
            }
            if (AllCommand::$db_update == false && $entity instanceof Nozology) {
                $this->updatedNozology();
            }

            if ($entity instanceof Publication) {
                $this->generatePublicationLink($args);
            }

            # высчитываем перелинковку
            if (($entity instanceof Art || $entity instanceof Article || $entity instanceof Publication)) {
                if ($entity instanceof Art) {
                    $command = new ArtLinkedCommand();
                }
                elseif ($entity instanceof Article) {
                    $command = new ArticleLinkedCommand();
                }
                else {
                    $command = new PublicationLinkedCommand();
                }
                $command->setContainer($this->container);
                $input = new ArrayInput(array('art_id' => $entity->getId()));
                $output = new NullOutput();
                $command->run($input, $output);
            }
        }
        catch (\Exception $e) {
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            if ($entity instanceof Product) {
                $em = $args->getEntityManager();
                $pdo = $em->getConnection();
                $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
                $stmt->execute();
                $stmt = $pdo->prepare('DELETE FROM product WHERE ProductID = ' . $entity->getProductID());
                $stmt->execute();
            }

            if ($entity instanceof InfoPage) {
                $em = $args->getEntityManager();
                $pdo = $em->getConnection();
                $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
                $stmt->execute();
                $stmt = $pdo->prepare('DELETE FROM tag WHERE InfoPageID = ' . $entity->getInfoPageID());
                $stmt->execute();
            }

            if ($entity instanceof \Vidal\DrugBundle\Entity\ProductCompany) {
                $em = $args->getEntityManager();
                $pdo = $em->getConnection();
                $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
                $stmt->execute();
                $stmt = $pdo->prepare('DELETE FROM product_company WHERE ProductID = ' . $entity->getProductID()->getProductID() . ' AND CompanyID = ' . $entity->getCompanyID()->getCompanyID());
                $stmt->execute();
            }

            /** @var User $user */
            if ($token = $this->container->get('security.context')->getToken()) {
                if ($user = $token->getUser()) {
                    if (in_array($user->getId(), self::$monitorAdminIds)) {
                        /** @var EntityManager $em */
                        $em = $this->container->get('doctrine')->getManager('default');
                        $log = new Log();
                        $log->setAdminEmail($user->getUsername());
                        $log->setAdminId($user->getId());
                        $log->setEvent(Log::EVENT_DELETE);
                        $log->setEntityClass(get_class($entity));
                        $log->setEntityId($entity->getId());
                        $em->persist($log);
                        $em->flush($log);
                    }
                }
            }
        }
        catch (\Exception $e) {
        }
    }

    private function setVideoMeta($entity)
    {
        $video = $entity->getVideo();

        if ($video && isset($video['path'])) {
            $id = $entity->getId();
            $rootDir = $this->container->get('kernel')->getRootDir() . '/../';
            require_once $rootDir . 'src/getID3/getid3.php';

            $getID3 = new \getID3;
            $filename = $rootDir . 'web' . $video['path'];
            $file = $getID3->analyze($filename);

            $x = $file['video']['resolution_x'];
            $y = $file['video']['resolution_y'];
            $pdo = $this->container->get('doctrine')->getEntityManager('drug')->getConnection();

            if ($entity instanceof AdsSlider) {
                $pdo->prepare("UPDATE ads_slider SET videoWidth = {$x}, videoHeight = {$y} WHERE id={$id}")->execute();
            }
            elseif ($entity instanceof Ads) {
                $pdo->prepare("UPDATE ads SET videoWidth = {$x}, videoHeight = {$y} WHERE id={$id}")->execute();
            }
            elseif ($entity instanceof Art) {
                $pdo->prepare("UPDATE art SET videoWidth = {$x}, videoHeight = {$y} WHERE id={$id}")->execute();
            }
            elseif ($entity instanceof Article) {
                $pdo->prepare("UPDATE article SET videoWidth = {$x}, videoHeight = {$y} WHERE id={$id}")->execute();
            }
        }
    }

    private function setLink($entity)
    {
        $link = $entity->getLink();

        if (empty($link)) {
            $link = $this->translit($entity->getTitle());
            $entity->setLink($link);
        }
    }

    private function translit($text)
    {
        $pat = array('/&[a-z]+;/', '/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i');
        $rep = array('', '$1', '$1');
        $text = preg_replace($pat, $rep, $text);
        $text = mb_strtolower($text, 'utf-8');

        // Русский алфавит
        $rus_alphabet = array(
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й',
            'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф',
            'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й',
            'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф',
            'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
            ' ', '.', '(', ')', ',', '/', '?'
        );

        // Английская транслитерация
        $rus_alphabet_translit = array(
            'A', 'B', 'V', 'G', 'D', 'E', 'IO', 'ZH', 'Z', 'I', 'Y',
            'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F',
            'H', 'TS', 'CH', 'SH', 'SCH', '', 'Y', '', 'E', 'YU', 'IA',
            'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y',
            'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f',
            'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ia',
            '-', '-', '-', '-', '-', '-', '-'
        );

        return str_replace($rus_alphabet, $rus_alphabet_translit, $text);
    }

    private function autocompleteProduct(Product $product)
    {
        # get names
        $RusName = $this->strip($product->getRusName());
        $RusName = mb_strtolower($RusName, 'UTF-8');
        $EngName = $this->strip($product->getEngName());
        $EngName = mb_strtolower($EngName, 'UTF-8');

        $client = ClientBuilder::create()->build();
        $elasticaClient = new \Elastica\Client();
        $elasticaIndex = $elasticaClient->getIndex('website');

        $s['index'] = 'website';
        $s['type'] = 'autocomplete';
        $s['body']['size'] = 1;
        $s['body']['query']['bool']['must']['query_string']['query'] = $RusName;
        $s['body']['query']['bool']['must']['query_string']['default_field'] = 'name';
        $s['body']['query']['bool']['filter']['term']['type'] = 'product';

        # total rus
        $results = $client->search($s);
        $totalRus = $results['hits']['total'];

        # total eng
        $s['body']['query']['bool']['must']['query_string']['query'] = $EngName;
        $results = $client->search($s);
        $totalEng = $results['hits']['total'];

        # autocomplete
        $type = $elasticaIndex->getType('autocomplete');
        if ($totalRus == 0) {
            $document = new \Elastica\Document(null, array('name' => $RusName, 'type' => 'product'));
            $type->addDocument($document);
        }
        if ($totalEng == 0) {
            $document = new \Elastica\Document(null, array('name' => $EngName, 'type' => 'product'));
            $type->addDocument($document);
        }
        $type->getIndex()->refresh();

        # autocomplete_ext
        $type = $elasticaIndex->getType('autocomplete_ext');
        if ($totalRus == 0) {
            $document = new \Elastica\Document(null, array('name' => $RusName, 'type' => 'product'));
            $type->addDocument($document);
        }
        if ($totalEng == 0) {
            $document = new \Elastica\Document(null, array('name' => $EngName, 'type' => 'product'));
            $type->addDocument($document);
        }
        $type->getIndex()->refresh();

        # product
        $type = $elasticaIndex->getType('autocomplete_product');
        if ($totalRus == 0) {
            $name = $RusName . ' ' . $product->getProductID();
            $document = new \Elastica\Document(null, array('name' => $name));
            $type->addDocument($document);
        }
        $type->getIndex()->refresh();

        return true;
    }

    private function strip($string)
    {
        $pat = array('/<sup>(.*?)<\/sup>/i', '/<sub>(.*?)<\/sub>/i', '/&amp;/');
        $rep = array('', '', '&');

        return preg_replace($pat, $rep, $string);
    }

    private function checkDuplicateDocument(LifecycleEventArgs $args)
    {
        $document = $args->getEntity();
        $DocumentID = $document->getDocumentID();
        $em = $args->getEntityManager();
        $documentInDb = $em->getRepository('VidalDrugBundle:Document')->findOneByDocumentID($DocumentID);

        $pdo = $em->getConnection();
        $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
        $stmt->execute();

        # если документ с таким идентификатором уже есть - его надо удалить, не проверяя внешних ключей
        if ($documentInDb) {
            $stmt = $pdo->prepare("DELETE FROM document WHERE DocumentID = $DocumentID");
            $stmt->execute();
        }

        # надо почистить старые связи документа
        $tables = explode(' ', 'document_indicnozology document_clphpointers documentoc_atc document_infopage art_document article_document molecule_document pharm_article_document publication_document');
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE DocumentID = {$DocumentID}");
            $stmt->execute();
        }
    }

    private function checkDuplicateProduct(LifecycleEventArgs $args)
    {
        $product = $args->getEntity();
        $ProductID = $product->getProductID();
        $em = $args->getEntityManager();
        $productInDb = $em->getRepository('VidalDrugBundle:Product')->findByProductID($ProductID);

        $pdo = $em->getConnection();
        $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
        $stmt->execute();

        # если продукт с таким идентификатором уже есть - его надо удалить, не проверяя внешних ключей
        if ($productInDb) {
            $stmt = $pdo->prepare("DELETE FROM product WHERE ProductID = $ProductID");
            $stmt->execute();
        }

        # надо почистить старые связи документа
        $tables = explode(' ', 'product_atc product_clphgroups product_company product_document product_moleculename product_phthgrp');
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE ProductID = {$ProductID}");
            $stmt->execute();
        }
    }

    private function generatePublicationLink(LifecycleEventArgs $args)
    {
        /** @var Publication $publication */
        $publication = $args->getEntity();
        $title = $publication->getTitle();
        $id = $publication->getId();
        $linkManual = $publication->getLinkManual();

        $pdo = $args->getEntityManager()->getConnection();
        $link = empty($linkManual) ? PublicationLinkCommand::ctl_sanitize_title($title) : $linkManual;
        $pdo->prepare("UPDATE publication SET link = '{$link}' WHERE id = {$id}")->execute();
    }

    private function checkDuplicateTitle(LifecycleEventArgs $args, $table)
    {
        try {
            $session = $this->container->get('session');
            $title = $args->getEntity()->getTitle();

            if ($session->has('title') && $session->get('title') == $title) {
                $pdo = $args->getEntityManager()->getConnection();
                $stmt = $pdo->prepare('SET FOREIGN_KEY_CHECKS=0');
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM $table WHERE title = '$title'");
                $stmt->execute();
            }

            $session->set('title', $title);
        }
        catch (\Exception $e) {

        }
    }

    private function updateCountProducts(LifecycleEventArgs $args)
    {
        ini_set('memory_limit', -1);

        $em = $args->getEntityManager();

        $repo = $em->getRepository('VidalDrugBundle:Product');
        $companies = $em->getRepository('VidalDrugBundle:Company')->findAll();

        # ставим сколько всего у них препаратов
        for ($i = 0; $i < count($companies); $i++) {
            $count = $repo->countByCompanyID($companies[$i]->getCompanyID());
            $companies[$i]->setCountProducts($count);
        }

        $em->flush();

        $infoPages = $em->getRepository('VidalDrugBundle:InfoPage')->findAll();

        # ставим сколько всего у них препаратов
        foreach ($infoPages as $infoPage) {
            $documentIds = $em->getRepository('VidalDrugBundle:Document')->findIdsByInfoPageID($infoPage->getInfoPageID());
            $count = $em->getRepository('VidalDrugBundle:Product')->countByDocumentIDs($documentIds);
            $infoPage->setCountProducts($count);
        }

        $em->flush();
    }

    private function transformUrl($s)
    {
        $s = str_replace('<SUP>', ' ', $s);
        $s = str_replace('</SUP>', '', $s);
        $s = str_replace('<SUB>', ' ', $s);
        $s = str_replace('</SUB>', '', $s);
        $s = str_replace('<BR/>', ' ', $s);
        $s = str_replace('<BR />', ' ', $s);
        $s = str_replace('<B>', ' ', $s);
        $s = str_replace('</B>', '', $s);
        $s = str_replace('&reg;', '', $s);
        $s = str_replace('&amp;', '', $s);
        $s = str_replace('&trade;', '', $s);
        $s = str_replace('&alpha;', '', $s);
        $s = str_replace('&beta;', '', $s);
        $s = str_replace('&plusmn;', '', $s);
        $s = str_replace('С', 'c', $s);
        $s = str_replace('с', 'c', $s);
        $s = str_replace('М', 'm', $s);
        $s = str_replace('м', 'm', $s);
        $s = str_replace('Т', 't', $s);
        $s = str_replace('т', 't', $s);
        $s = str_replace('Е', 'e', $s);
        $s = str_replace('е', 'e', $s);
        $s = str_replace('Н', 'h', $s);
        $s = str_replace('н', 'h', $s);
        $s = str_replace('В', 'b', $s);
        $s = str_replace('в', 'b', $s);
        $s = str_replace('К', 'k', $s);
        $s = str_replace('к', 'k', $s);
        $s = str_replace('Р', 'p', $s);
        $s = str_replace('Р', 'p', $s);
        $s = str_replace('А', 'a', $s);
        $s = str_replace('а', 'a', $s);
        $s = str_replace('О', 'o', $s);
        $s = str_replace('о', 'o', $s);
        $s = str_replace('(', ' ', $s);
        $s = str_replace(')', ' ', $s);
        $s = str_replace('+', ' ', $s);
        $s = str_replace('№', ' ', $s);
        $s = str_replace('"', '', $s);
        $s = str_replace("'", '', $s);
        $s = str_replace('%', '', $s);
        $s = str_replace('.', ' ', $s);
        $s = str_replace(',', ' ', $s);
        $s = str_replace('/', ' ', $s);
        $s = str_replace(' - ', ' ', $s);
        $s = str_replace('_', ' ', $s);
        $s = str_replace('  ', ' ', $s);

        $s = str_replace(' ', '-', $s);
        $s = str_replace('--', '-', $s);

        $s = strtolower($s);
        $s = trim($s, '-');
        $s = preg_replace('/[^\da-z-]/i', '', $s);

        return $s;
    }

    private function setProductUrl(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        /** @var Product $product */
        $product = $args->getEntity();

        $url = $product->getUrl();

        if (empty($url)) {
            $url = $this->transformUrl($product->getEngName());
        }

        $productByUrl = $em->getRepository('VidalDrugBundle:Product')->findByUrl($url);

        if (null == $productByUrl) {
            $product->setUrl($url);
        }
        else {
            $numbers = explode(' ', '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15');
            foreach ($numbers as $number) {
                $tryUrl = $url . '-' . $number;
                if (null == $em->getRepository('VidalDrugBundle:Product')->findByUrl($tryUrl)) {
                    $product->setUrl($tryUrl);
                    break;
                }
            }
        }
    }

    private function updateProductUrl(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        /** @var Product $product */
        $product = $args->getEntity();

        $url = $product->getUrl();

        if (!empty($url)) {
            $productByUrl = $em->getRepository('VidalDrugBundle:Product')->findByUriWithoutProduct($url, $product->getId());

            if (null == $productByUrl) {
                $product->setUrl($url);
                $product->setUri($url);
                $em->flush($product);
                $emDefault = $this->container->get('doctrine')->getManager('default');
                $emDefault->getRepository("VidalMainBundle:DrugInfo")->updateProductUri($product->getProductID(), $url);
            }
            else {
                $numbers = explode(' ', '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15');
                foreach ($numbers as $number) {
                    $tryUrl = $url . '-' . $number;
                    if (null == $em->getRepository('VidalDrugBundle:Product')->findByUri($tryUrl)) {
                        $product->setUrl($tryUrl);
                        $product->setUri($tryUrl);
                        $em->flush($product);
                        $emDefault = $this->container->get('doctrine')->getManager('default');
                        $emDefault->getRepository("VidalMainBundle:DrugInfo")->updateProductUri($product->getProductID(), $tryUrl);
                        break;
                    }
                }
            }
        }
    }

    private function productMainID(LifecycleEventArgs $args)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager('default');

        $keyValue = $em->getRepository("VidalMainBundle:KeyValue")->getByKey(KeyValue::START_PRODUCT_MAIN);
        $keyValue->setValue('start');
        $em->flush($keyValue);

        $this->container->get('session')->getFlashbag()
            ->add('msg', 'Обновление связей основных/дочерних препаратов произойдет в течении 5 минут');
    }

    private function updateProductNames(LifecycleEventArgs $args)
    {
        /** @var EntityManager $em */
        $em = $args->getEntityManager();
        $pdo = $em->getConnection();
        /** @var Product $product */
        $product = $args->getObject();
        $ProductID = $product->getProductID();

        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName,'<SUP>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<sup>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</SUP>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</sup>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<SUB>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<sub>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</SUB>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'</sub>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<BR/>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<br/>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<BR/>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'<br/>','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&reg;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&reg','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&amp;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&trade;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&alpha;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&beta;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'&plusmn;','') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'  ',' ') WHERE ProductID = $ProductID")->execute();
        $pdo->prepare("UPDATE product SET RusName2 = REPLACE(RusName2,'\"','') WHERE ProductID = $ProductID")->execute();
    }

    private function updatedKfu()
    {
        /** @var ContainerAwareCommand[] $commands */
        $commands = array(
            (new KfuCountCommand()),
            (new KfuNameCommand()),
            (new KfuUrlCommand()),
            (new GeneratorKfuCommand()),
        );

        foreach ($commands as $command) {
            $command->setContainer($this->container);
            $input = new ArrayInput(array());
            $output = new NullOutput();
            $command->run($input, $output);
        }
    }

    private function updatedAtc()
    {
        /** @var ContainerAwareCommand[] $commands */
        $commands = array(
            (new AtcCountCommand()),
            (new AtcParentCommand()),
            (new GeneratorAtcCommand()),
        );

        foreach ($commands as $command) {
            $command->setContainer($this->container);
            $input = new ArrayInput(array());
            $output = new NullOutput();
            $command->run($input, $output);
        }
    }

    private function updatedNozology()
    {
        /** @var ContainerAwareCommand[] $commands */
        $commands = array(
            (new NozologyClassCommand()),
            (new NozologyCodeCommand()),
            (new NozologyLevelCommand()),
            (new NozologyNameCommand()),
            (new NozologyCountCommand()),
            (new GeneratorNozologyCommand()),
        );

        foreach ($commands as $command) {
            $command->setContainer($this->container);
            $input = new ArrayInput(array());
            $output = new NullOutput();
            $command->run($input, $output);
        }
    }
}
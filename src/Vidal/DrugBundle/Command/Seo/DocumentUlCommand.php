<?php

namespace Vidal\DrugBundle\Command\Seo;

use Vidal\MainBundle\Command\Seo\SslReplace;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentUlCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('vidal:seo:documen_ul')
            ->addArgument('id', InputArgument::OPTIONAL, 'Document id')
            ->setDescription('Меняет <p> на <ul> в полях документа');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Документ 250
        //369
        ini_set('memory_limit', -1);
        $output->writeln('<info>--- vidal:seo:documen_ul started</info>');
        $container = $this->getContainer();
        $documentId = $input->getArgument('id');

        /** @var EntityManager $emDrug */
        $emDrug = $container->get('doctrine')->getEntityManager('drug');

        $pdo = $emDrug->getConnection();

        $documents = empty($documentId)
            ? $emDrug->getRepository("VidalDrugBundle:Document")->findAll()
            : array($emDrug->getRepository("VidalDrugBundle:Document")->findOneById($documentId));

        $updatedItems = [];
        if (!empty($documents)) {
            $total = count($documents);
            foreach ($documents as $i => $document) {
                $id = $document->getId();

                // Описание, Форма выпуска, состав и упаковка
                $compiledComposition = $document->getCompiledComposition();
                $compiledCompositionNew = $this->findUl($compiledComposition);

                if ($compiledComposition != $compiledCompositionNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET compiledComposition = ? WHERE DocumentID = ?")->execute(
                        array($compiledCompositionNew, $id)
                    );
                }

                //Клинико-фарм. группа
                $clPhGrDescription = $document->getClPhGrDescription();
                $clPhGrDescriptionNew = $this->findUl($clPhGrDescription);

                if ($clPhGrDescription != $clPhGrDescriptionNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET clPhGrDescription = ? WHERE DocumentID = ?")->execute(
                        array($clPhGrDescriptionNew, $id)
                    );
                }

                //Фарм. действие
                $phInfluence = $document->getPhInfluence();
                $phInfluenceNew = $this->findUl($phInfluence);

                if ($phInfluence != $phInfluenceNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET phInfluence = ? WHERE DocumentID = ?")->execute(
                        array($phInfluenceNew, $id)
                    );
                }

                //Фармакокинетика
                $phKinetics = $document->getPhKinetics();
                $phKineticsNew = $this->findUl($phKinetics);

                if ($phKinetics != $phKineticsNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET phKinetics = ? WHERE DocumentID = ?")->execute(
                        array($phKineticsNew, $id)
                    );
                }

                //Режим дозирования
                $dosage = $document->getDosage();
                $dosageNew = $this->findUl($dosage);

                if ($dosage != $dosageNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET dosage = ? WHERE DocumentID = ?")->execute(
                        array($dosageNew, $id)
                    );
                }

                //Передозировка
                $overDosage = $document->getOverDosage();
                $overDosageNew = $this->findUl($overDosage);

                if ($overDosage != $overDosageNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET overDosage = ? WHERE DocumentID = ?")->execute(
                        array($overDosageNew, $id)
                    );
                }

                //Лекарственное взаимодействие
                $interaction = $document->getInteraction();
                $interactionNew = $this->findUl($interaction);

                if ($interaction != $interactionNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET interaction = ? WHERE DocumentID = ?")->execute(
                        array($interactionNew, $id)
                    );
                }

                //Применение при беременности и кормлении грудью
                $lactation = $document->getLactation();
                $lactationNew = $this->findUl($lactation);

                if ($lactation != $lactationNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET lactation = ? WHERE DocumentID = ?")->execute(
                        array($lactationNew, $id)
                    );
                }

                //Побочное действие
                $sideEffects = $document->getSideEffects();
                $sideEffectsNew = $this->findUl($sideEffects);

                if ($sideEffects != $sideEffectsNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET sideEffects = ? WHERE DocumentID = ?")->execute(
                        array($sideEffectsNew, $id)
                    );
                }

                //Условия и сроки хранения
                $storageCondition = $document->getStorageCondition();
                $storageConditionNew = $this->findUl($storageCondition);

                if ($storageCondition != $storageConditionNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET storageCondition = ? WHERE DocumentID = ?")->execute(
                        array($storageConditionNew, $id)
                    );
                }

                //Показания
                $indication = $document->getIndication();
                $indicationNew = $this->findUl($indication);

                if ($indication != $indicationNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET indication = ? WHERE DocumentID = ?")->execute(
                        array($indicationNew, $id)
                    );
                }

                //Противопоказания
                $contraIndication = $document->getContraIndication();
                $contraIndicationNew = $this->findUl($contraIndication);
                if ($contraIndication != $contraIndicationNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET contraIndication = ? WHERE DocumentID = ?")->execute(
                        array($contraIndicationNew, $id)
                    );
                }

                //Условия отпуска из аптек
                $pharmDelivery = $document->getPharmDelivery();
                $pharmDeliveryNew = $this->findUl($pharmDelivery);

                if ($pharmDelivery != $pharmDeliveryNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET pharmDelivery = ? WHERE DocumentID = ?")->execute(
                        array($pharmDeliveryNew, $id)
                    );
                }

                //Особые указания
                $specialInstruction = $document->getSpecialInstruction();
                $specialInstructionNew = $this->findUl($specialInstruction);

                if ($specialInstruction != $specialInstructionNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET specialInstruction = ? WHERE DocumentID = ?")->execute(
                        array($specialInstructionNew, $id)
                    );
                }

                //Нарушения функции почек
                $renalInsuf = $document->getRenalInsuf();
                $renalInsufNew = $this->findUl($renalInsuf);

                if ($renalInsuf != $renalInsufNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET renalInsuf = ? WHERE DocumentID = ?")->execute(
                        array($renalInsufNew, $id)
                    );
                }

                //Нарушение функции печени
                $hepatoInsuf = $document->getHepatoInsuf();
                $hepatoInsufNew = $this->findUl($hepatoInsuf);

                if ($hepatoInsuf != $hepatoInsufNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET hepatoInsuf = ? WHERE DocumentID = ?")->execute(
                        array($hepatoInsufNew, $id)
                    );
                }

                //Использование пожилыми пациентами
                $elderlyInsuf = $document->getElderlyInsuf();
                $elderlyInsufNew = $this->findUl($elderlyInsuf);

                if ($elderlyInsuf != $elderlyInsufNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET elderlyInsuf = ? WHERE DocumentID = ?")->execute(
                        array($elderlyInsufNew, $id)
                    );
                }

                //Использование детьми
                $childInsuf = $document->getChildInsuf();
                $childInsufNew = $this->findUl($childInsuf);

                if ($childInsuf != $childInsufNew) {
                    $updatedItems[$id] = true;
                    $pdo->prepare("UPDATE document SET childInsuf = ? WHERE DocumentID = ?")->execute(
                        array($childInsufNew, $id)
                    );
                }
            }
        }

        $updatedItemsCount = count($updatedItems);
        $output->writeln("<comment>updated {$updatedItemsCount} items</comment>");
        $output->writeln('<info>--- vidal:seo:documen_ul finished</info>');
    }

    /**
     * Ищет <p> и заменяет <ul> в тексте
     * 
     * @access protected
     * @param string $text
     * @return string
     */
    protected function findUl($text)
    {
        $textNew = str_replace("<P ", "<p ", $text);
        $textNew = str_replace("<P>", "<p>", $textNew);
        $textNew = str_replace("</P>", "</p>", $textNew);
        $textNew = str_replace("<p>&mdash;", "<p>- ", $textNew);
        $hasList = false;

        if (strpos($textNew, '<p>- ') !== false) {
            $hasList = true;
        }

        if($hasList) {
            preg_match_all('/<p>- (.*?)<\/p>/s', $textNew, $matches);
            if(isset($matches[0]) && count($matches[0]) > 0) {
                foreach($matches[0] as $match) {
                    $matchNew = str_replace("<p>- ", "<li>", $match);
                    $matchNew = str_replace("</p>", "</li>", $matchNew);

                    $textNew = str_replace($match, $matchNew, $textNew);
                }
            }


            $listLi = explode("</li>", $textNew);
            foreach($listLi as $index => $li) {
                if($index >0 && isset($listLi[$index-1])) {
                    if (strpos($listLi[$index-1], '<li>') !== false) {
                        if (strpos($li, '<p>') !== false) {
                            $liNew = "</ul>".$li;
                            $textNew = str_replace($li, $liNew, $textNew);
                        }
                    }
                }

                if (strpos($li, '<p>') !== false) {
                    $liNew = str_replace('<li>', '<ul><li>', $li);
                    $textNew = str_replace($li, $liNew, $textNew);
                }
            }
            $textNew = $this->closetags($textNew);
            if (strpos($textNew, '<li>') !== false) {
                if (strpos($textNew, '<ul>') === false) {
                    $textNew  ="<ul>".$textNew."</ul>";
                }
            }

            return $textNew;
        }

        return $text;
    }

    /**
     * Закрывает теги в html
     * 
     * @access protected
     * @param string $html
     * @return string
     */
    protected function closetags($html)
    {
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                if($openedtags[$i]!='br' && $openedtags[$i]!='BR') {
                    $html .= '</'.$openedtags[$i].'>';
                }
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    }
}
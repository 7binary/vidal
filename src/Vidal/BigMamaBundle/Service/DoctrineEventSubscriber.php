<?php

namespace Vidal\BigMamaBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Vidal\BigMamaBundle\Command\PublicationLinkCommand;
use Vidal\BigMamaBundle\Entity\Audio;
use Vidal\BigMamaBundle\Entity\Publication;
use Vidal\BigMamaBundle\Entity\Video;
use Vidal\BigMamaBundle\Entity\Specialist;

class DoctrineEventSubscriber implements EventSubscriber
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'postUpdate',
        );
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            if ($entity instanceof Publication) {
                $this->updateLink($args, 'big_mama_publication');
            }
            if ($entity instanceof Audio) {
                $this->updateLink($args, 'big_mama_audio');
            }
            if ($entity instanceof Video) {
                $this->updateLink($args, 'big_mama_video');
            }
            if ($entity instanceof Specialist) {
                $this->updateLink($args, 'big_mama_specialist');
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        try {
            $entity = $args->getEntity();

            if ($entity instanceof Publication) {
                $this->updateLink($args, 'big_mama_publication');
            }
            if ($entity instanceof Audio) {
                $this->updateLink($args, 'big_mama_audio');
            }
            if ($entity instanceof Video) {
                $this->updateLink($args, 'big_mama_video');
            }
            if ($entity instanceof Specialist) {
                $this->updateLink($args, 'big_mama_specialist');
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    private function updateLink(LifecycleEventArgs $args, $table)
    {
        $model = $args->getEntity();
        $title = $model->getTitle();
        $id = $model->getId();
        $linkManual = $model->getLinkManual();

        $pdo = $args->getEntityManager()->getConnection();
        $link = empty($linkManual) ? PublicationLinkCommand::ctl_sanitize_title($title) : $linkManual;

        $pdo->prepare("UPDATE {$table} SET link = '{$link}' WHERE id = {$id}")->execute();
    }
}
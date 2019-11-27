<?php

namespace Vidal\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MailboxRepository extends EntityRepository
{
    public function getMessageIds()
    {
        $raw = $this->_em->createQuery('
			SELECT m.messageId
			FROM VidalMainBundle:Mailbox m
			ORDER BY m.id
		')->getResult();

        $ids = array();
        foreach ($raw as $r) {
            $ids[] = $r['messageId'];
        }

        return $ids;
    }
}
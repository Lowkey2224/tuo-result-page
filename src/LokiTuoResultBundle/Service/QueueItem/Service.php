<?php

namespace App\LokiTuoResultBundle\Service\QueueItem;

use App\LokiTuoResultBundle\Entity\Player;
use App\LokiTuoResultBundle\Entity\QueueItem;
use Doctrine\ORM\EntityManager;
use LokiUserBundle\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

class Service
{
    /** @var EntityManager */
    private $em;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(EntityManager $entityManager, TranslatorInterface $translator)
    {
        $this->em = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @param User $user
     * @param Player $player
     * @return QueueItem
     */
    public function createItem(User $user, Player $player, string $message)
    {
        $msg = $this->translator->trans($message . " %name%", ["%name%" => $player->getName()]);
        $queueItem = new QueueItem();
        $queueItem->setUpdatedBy($user)
            ->setStatusWaiting()
            ->setMessage($msg);
        $this->em->persist($queueItem);
        $this->em->flush($queueItem);
        $this->em->refresh($queueItem);
        return $queueItem;
    }

    public function getItem(int $itemId)
    {
        return $this->em->find(QueueItem::class, $itemId);
    }

    public function setStatusRunning(QueueItem $item)
    {
        $item->setStatusRunning();
        $this->em->persist($item);
        $this->em->flush($item);
        $this->em->refresh($item);
        return $item;
    }

    public function setStatusFinished(QueueItem $item)
    {
        $item->setStatusFinished();
        $this->em->persist($item);
        $this->em->flush($item);
        $this->em->refresh($item);
        return $item;
    }
}

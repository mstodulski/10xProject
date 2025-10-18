<?php

namespace App\EventListener;

use App\Entity\Inspection;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::prePersist, entity: Inspection::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Inspection::class)]
class InspectionEntityListener
{
    /**
     * Automatically sets end_datetime to 30 minutes after start_datetime
     * before persisting or updating an Inspection entity.
     */
    public function prePersist(Inspection $inspection, LifecycleEventArgs $event): void
    {
        $this->setEndDatetime($inspection);
    }

    public function preUpdate(Inspection $inspection, LifecycleEventArgs $event): void
    {
        $this->setEndDatetime($inspection);
    }

    private function setEndDatetime(Inspection $inspection): void
    {
        $startDatetime = $inspection->getStartDatetime();
        $endDatetime = $startDatetime->add(new DateInterval('PT30M'));
        $inspection->setEndDatetime($endDatetime);
    }
}

<?php

namespace App\Repository;

use App\Entity\Inspection;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inspection>
 */
class InspectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inspection::class);
    }

    /**
     * Find all inspections for a specific date
     */
    public function findByDate(DateTimeImmutable $date): array
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        return $this->createQueryBuilder('i')
            ->where('i.startDatetime >= :start')
            ->andWhere('i.startDatetime <= :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->orderBy('i.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all inspections for a specific week
     */
    public function findByWeek(DateTimeImmutable $weekStart): array
    {
        $weekEnd = $weekStart->modify('+6 days')->setTime(23, 59, 59);

        return $this->createQueryBuilder('i')
            ->where('i.startDatetime >= :start')
            ->andWhere('i.startDatetime <= :end')
            ->setParameter('start', $weekStart->setTime(0, 0, 0))
            ->setParameter('end', $weekEnd)
            ->orderBy('i.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all inspections in a date range
     */
    public function findByDateRange(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.startDatetime >= :start')
            ->andWhere('i.startDatetime <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('i.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all future inspections
     */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.startDatetime > :now')
            ->setParameter('now', new DateTimeImmutable())
            ->orderBy('i.startDatetime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all past inspections
     */
    public function findPast(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.startDatetime < :now')
            ->setParameter('now', new DateTimeImmutable())
            ->orderBy('i.startDatetime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all inspections created by a specific user
     */
    public function findByCreator(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.createdByUser = :user')
            ->setParameter('user', $user)
            ->orderBy('i.startDatetime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if there's a time conflict with existing inspections
     * Returns true if there IS a conflict, false if the time slot is available
     *
     * @param DateTimeImmutable $startDatetime Start time of the new inspection
     * @param DateTimeImmutable $endDatetime End time of the new inspection
     * @param int|null $excludeInspectionId Optional ID to exclude (for updates)
     * @return bool True if there's a conflict, false if available
     */
    public function hasTimeConflict(
        DateTimeImmutable $startDatetime,
        DateTimeImmutable $endDatetime,
        ?int $excludeInspectionId = null
    ): bool {
        // Add 15-minute buffer before and after each inspection
        $bufferMinutes = 15;
        $newStart = $startDatetime->modify("-{$bufferMinutes} minutes");
        $newEnd = $endDatetime->modify("+{$bufferMinutes} minutes");

        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.startDatetime < :newEnd')
            ->andWhere('i.endDatetime > :newStart')
            ->setParameter('newStart', $newStart)
            ->setParameter('newEnd', $newEnd);

        // Exclude specific inspection (useful for updates)
        if ($excludeInspectionId !== null) {
            $qb->andWhere('i.id != :excludeId')
                ->setParameter('excludeId', $excludeInspectionId);
        }

        $count = (int) $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Find inspections that conflict with a given time slot
     */
    public function findConflictingInspections(
        DateTimeImmutable $startDatetime,
        DateTimeImmutable $endDatetime,
        ?int $excludeInspectionId = null
    ): array {
        // Add 15-minute buffer
        $bufferMinutes = 15;
        $newStart = $startDatetime->modify("-{$bufferMinutes} minutes");
        $newEnd = $endDatetime->modify("+{$bufferMinutes} minutes");

        $qb = $this->createQueryBuilder('i')
            ->where('i.startDatetime < :newEnd')
            ->andWhere('i.endDatetime > :newStart')
            ->setParameter('newStart', $newStart)
            ->setParameter('newEnd', $newEnd)
            ->orderBy('i.startDatetime', 'ASC');

        if ($excludeInspectionId !== null) {
            $qb->andWhere('i.id != :excludeId')
                ->setParameter('excludeId', $excludeInspectionId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get inspections count for a specific date
     */
    public function countByDate(DateTimeImmutable $date): int
    {
        $startOfDay = $date->setTime(0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59);

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.startDatetime >= :start')
            ->andWhere('i.startDatetime <= :end')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get today's inspections
     */
    public function findToday(): array
    {
        return $this->findByDate(new DateTimeImmutable());
    }

    /**
     * Find inspections for current week (Monday to Sunday)
     */
    public function findCurrentWeek(): array
    {
        $now = new DateTimeImmutable();
        $weekStart = $now->modify('monday this week')->setTime(0, 0, 0);

        return $this->findByWeek($weekStart);
    }

    /**
     * Find next available time slot on a given date
     * Returns array with 'start' and 'end' DateTimeImmutable objects, or null if no slots available
     */
    public function findNextAvailableSlot(DateTimeImmutable $date): ?array
    {
        $inspections = $this->findByDate($date);

        // Working hours: 07:00 - 16:00
        $workStart = $date->setTime(7, 0, 0);
        $workEnd = $date->setTime(16, 0, 0);

        // Duration of inspection + buffer
        $slotDuration = 30; // 30 minutes for inspection
        $bufferDuration = 15; // 15 minutes buffer

        $currentTime = $workStart;

        foreach ($inspections as $inspection) {
            $proposedEnd = $currentTime->modify("+{$slotDuration} minutes");
            $slotEnd = $currentTime->modify("+{$slotDuration} minutes +{$bufferDuration} minutes");

            // Check if proposed slot fits before next inspection
            if ($slotEnd <= $inspection->getStartDatetime()) {
                return [
                    'start' => $currentTime,
                    'end' => $proposedEnd
                ];
            }

            // Move to after this inspection + buffer
            $currentTime = $inspection->getEndDatetime()->modify("+{$bufferDuration} minutes");
        }

        // Check if there's a slot at the end of the day
        $proposedEnd = $currentTime->modify("+{$slotDuration} minutes");
        if ($proposedEnd <= $workEnd) {
            return [
                'start' => $currentTime,
                'end' => $proposedEnd
            ];
        }

        return null;
    }

    /**
     * Get statistics: total inspections count
     */
    public function getTotalCount(): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get statistics: inspections count by consultant
     */
    public function getCountByConsultant(User $consultant): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.createdByUser = :user')
            ->setParameter('user', $consultant)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find inspections with filters and pagination
     *
     * @return array{inspections: Inspection[], total: int}
     */
    public function findWithFiltersAndPagination(
        ?DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate,
        ?int $createdByUserId,
        int $page,
        int $limit
    ): array {
        $offset = ($page - 1) * $limit;

        // Query builder for inspections
        $qb = $this->createQueryBuilder('i')
            ->select('i', 'u')  // Select both inspection and user to avoid N+1
            ->innerJoin('i.createdByUser', 'u');

        // Apply filters
        if ($startDate !== null) {
            $qb->andWhere('i.startDatetime >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate !== null) {
            // End of the end date (23:59:59)
            $endOfDay = $endDate->setTime(23, 59, 59);
            $qb->andWhere('i.startDatetime <= :endDate')
                ->setParameter('endDate', $endOfDay);
        }

        if ($createdByUserId !== null) {
            $qb->andWhere('i.createdByUser = :userId')
                ->setParameter('userId', $createdByUserId);
        }

        // Clone query builder for count
        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(i.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination and ordering
        $inspections = $qb
            ->orderBy('i.startDatetime', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'inspections' => $inspections,
            'total' => $total
        ];
    }
}

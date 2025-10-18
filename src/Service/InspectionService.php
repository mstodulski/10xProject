<?php

namespace App\Service;

use App\Dto\InspectionListQueryDto;
use App\Dto\InspectionListResponseDto;
use App\Dto\InspectionResponseDto;
use App\Dto\PaginationMetaDto;
use App\Repository\InspectionRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class InspectionService
{
    public function __construct(
        private readonly InspectionRepository $inspectionRepository,
        private readonly UserRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get inspections list with filters and pagination
     *
     * @throws InvalidArgumentException
     */
    public function getInspections(InspectionListQueryDto $query): InspectionListResponseDto
    {
        // Validate date range
        if ($query->startDate !== null && $query->endDate !== null) {
            $start = DateTimeImmutable::createFromFormat('Y-m-d', $query->startDate);
            $end = DateTimeImmutable::createFromFormat('Y-m-d', $query->endDate);

            if ($start === false || $end === false) {
                throw new InvalidArgumentException(
                    'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
                );
            }

            if ($start > $end) {
                throw new InvalidArgumentException(
                    'Data rozpoczęcia nie może być późniejsza niż data zakończenia'
                );
            }
        }

        // Validate user exists if provided
        if ($query->createdByUserId !== null) {
            $user = $this->userRepository->find($query->createdByUserId);
            if ($user === null) {
                throw new InvalidArgumentException(
                    'Nie znaleziono użytkownika o podanym ID'
                );
            }
        }

        // Convert string dates to DateTimeImmutable
        $startDate = $query->startDate !== null
            ? DateTimeImmutable::createFromFormat('Y-m-d', $query->startDate)->setTime(0, 0, 0)
            : null;
        $endDate = $query->endDate !== null
            ? DateTimeImmutable::createFromFormat('Y-m-d', $query->endDate)->setTime(23, 59, 59)
            : null;

        // Fetch data from repository
        $result = $this->inspectionRepository->findWithFiltersAndPagination(
            startDate: $startDate,
            endDate: $endDate,
            createdByUserId: $query->createdByUserId,
            page: $query->page,
            limit: $query->limit
        );

        // Transform entities to DTOs
        $inspectionDtos = array_map(
            fn($inspection) => InspectionResponseDto::fromEntity($inspection),
            $result['inspections']
        );

        // Calculate pagination metadata
        $totalPages = (int) ceil($result['total'] / $query->limit);
        $meta = new PaginationMetaDto(
            currentPage: $query->page,
            perPage: $query->limit,
            total: $result['total'],
            totalPages: $totalPages
        );

        $this->logger->info('Inspections list fetched', [
            'filters' => [
                'startDate' => $query->startDate,
                'endDate' => $query->endDate,
                'createdByUserId' => $query->createdByUserId
            ],
            'pagination' => [
                'page' => $query->page,
                'limit' => $query->limit,
                'total' => $result['total']
            ]
        ]);

        return new InspectionListResponseDto(
            data: $inspectionDtos,
            meta: $meta
        );
    }
}

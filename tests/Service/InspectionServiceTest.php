<?php

namespace App\Tests\Service;

use App\Dto\InspectionListQueryDto;
use App\Dto\InspectionListResponseDto;
use App\Dto\InspectionResponseDto;
use App\Dto\PaginationMetaDto;
use App\Entity\Inspection;
use App\Entity\User;
use App\Repository\InspectionRepository;
use App\Repository\UserRepository;
use App\Service\InspectionService;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * PHPUnit tests for InspectionService::getInspections() method
 *
 * Tests cover:
 * - Successful retrieval scenarios with various filters
 * - Validation of input parameters
 * - Edge cases (empty results, pagination boundaries)
 * - Error handling
 * - Logging verification
 *
 * All tests use mocks instead of real database connections
 */
class InspectionServiceTest extends TestCase
{
    private InspectionRepository&MockObject $inspectionRepository;
    private UserRepository&MockObject $userRepository;
    private LoggerInterface&MockObject $logger;
    private InspectionService $inspectionService;

    /**
     * Set up test dependencies before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for all dependencies
        $this->inspectionRepository = $this->createMock(InspectionRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // Create service instance with mocked dependencies
        $this->inspectionService = new InspectionService(
            $this->inspectionRepository,
            $this->userRepository,
            $this->logger
        );
    }

    /**
     * Test successful retrieval of inspections without any filters
     * TC-001: Pobieranie listy oględzin bez filtrów
     */
    public function testGetInspectionsWithoutFilters(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 1;
        $queryDto->limit = 50;

        $mockInspections = [
            $this->createMockInspection(1),
            $this->createMockInspection(2),
            $this->createMockInspection(3),
        ];

        $repositoryResult = [
            'inspections' => $mockInspections,
            'total' => 3,
        ];

        // Set expectations
        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->isNull(), // startDate
                $this->isNull(), // endDate
                $this->isNull(), // createdByUserId
                1, // page
                50  // limit
            )
            ->willReturn($repositoryResult);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Inspections list fetched',
                $this->callback(function ($context) {
                    return isset($context['filters'])
                        && isset($context['pagination'])
                        && $context['pagination']['total'] === 3;
                })
            );

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
        $this->assertCount(3, $result->data);
        $this->assertContainsOnlyInstancesOf(InspectionResponseDto::class, $result->data);
        $this->assertEquals(1, $result->meta->currentPage);
        $this->assertEquals(50, $result->meta->perPage);
        $this->assertEquals(3, $result->meta->total);
        $this->assertEquals(1, $result->meta->totalPages);
    }

    /**
     * Test retrieval with date range filter
     * TC-002: Pobieranie listy z filtrem dat
     */
    public function testGetInspectionsWithDateFilter(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-15';
        $queryDto->endDate = '2025-10-22';
        $queryDto->page = 1;
        $queryDto->limit = 50;

        $repositoryResult = [
            'inspections' => [$this->createMockInspection(1)],
            'total' => 1,
        ];

        // Set expectations - verify dates are properly converted
        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->callback(function ($startDate) {
                    return $startDate instanceof DateTimeImmutable
                        && $startDate->format('Y-m-d H:i:s') === '2025-10-15 00:00:00';
                }),
                $this->callback(function ($endDate) {
                    return $endDate instanceof DateTimeImmutable
                        && $endDate->format('Y-m-d H:i:s') === '2025-10-22 23:59:59';
                }),
                $this->isNull(),
                1,
                50
            )
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
        $this->assertCount(1, $result->data);
    }

    /**
     * Test retrieval with user filter
     * TC-003: Pobieranie listy z filtrem użytkownika
     */
    public function testGetInspectionsWithUserFilter(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->createdByUserId = 1;
        $queryDto->page = 1;
        $queryDto->limit = 50;

        $mockUser = $this->createMockUser(1, 'Jan Kowalski');

        $repositoryResult = [
            'inspections' => [$this->createMockInspection(1)],
            'total' => 1,
        ];

        // Set expectations
        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($mockUser);

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->isNull(),
                $this->isNull(),
                1, // createdByUserId
                1,
                50
            )
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
        $this->assertCount(1, $result->data);
    }

    /**
     * Test retrieval with empty results
     * TC-004: Pobieranie pustej listy
     */
    public function testGetInspectionsWithEmptyResults(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 1;
        $queryDto->limit = 50;

        $repositoryResult = [
            'inspections' => [],
            'total' => 0,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
        $this->assertCount(0, $result->data);
        $this->assertEquals(0, $result->meta->total);
        $this->assertEquals(0, $result->meta->totalPages);
    }

    /**
     * Test pagination - first page
     * TC-005: Paginacja - pierwsza strona
     */
    public function testGetInspectionsPaginationFirstPage(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 1;
        $queryDto->limit = 10;

        $mockInspections = array_map(
            fn($i) => $this->createMockInspection($i),
            range(1, 10)
        );

        $repositoryResult = [
            'inspections' => $mockInspections,
            'total' => 25,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertEquals(1, $result->meta->currentPage);
        $this->assertEquals(10, $result->meta->perPage);
        $this->assertEquals(25, $result->meta->total);
        $this->assertEquals(3, $result->meta->totalPages); // ceil(25/10) = 3
        $this->assertCount(10, $result->data);
    }

    /**
     * Test pagination - last page with partial results
     * TC-006: Paginacja - ostatnia strona
     */
    public function testGetInspectionsPaginationLastPage(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 3;
        $queryDto->limit = 10;

        $mockInspections = array_map(
            fn($i) => $this->createMockInspection($i),
            range(1, 5)
        );

        $repositoryResult = [
            'inspections' => $mockInspections,
            'total' => 25,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertEquals(3, $result->meta->currentPage);
        $this->assertEquals(10, $result->meta->perPage);
        $this->assertEquals(25, $result->meta->total);
        $this->assertEquals(3, $result->meta->totalPages);
        $this->assertCount(5, $result->data);
    }

    /**
     * Test validation - invalid start date format
     * TC-007: Nieprawidłowy format daty startowej
     */
    public function testGetInspectionsInvalidStartDateFormat(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = 'invalid-date';
        $queryDto->endDate = '2025-10-22';

        // Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD');

        // Act
        $this->inspectionService->getInspections($queryDto);
    }

    /**
     * Test validation - invalid end date format
     * TC-008: Nieprawidłowy format daty końcowej
     */
    public function testGetInspectionsInvalidEndDateFormat(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-15';
        $queryDto->endDate = '22-10-2025'; // Wrong format

        // Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nieprawidłowy format daty');

        // Act
        $this->inspectionService->getInspections($queryDto);
    }

    /**
     * Test validation - start date after end date
     * TC-009: Data rozpoczęcia późniejsza niż data zakończenia
     */
    public function testGetInspectionsStartDateAfterEndDate(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-22';
        $queryDto->endDate = '2025-10-15';

        // Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data rozpoczęcia nie może być późniejsza niż data zakończenia');

        // Act
        $this->inspectionService->getInspections($queryDto);
    }

    /**
     * Test validation - non-existent user
     * TC-010: Nieistniejący użytkownik
     */
    public function testGetInspectionsNonExistentUser(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->createdByUserId = 999;

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Expect exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nie znaleziono użytkownika o podanym ID');

        // Act
        $this->inspectionService->getInspections($queryDto);
    }

    /**
     * Test logging of successful operation
     * TC-011: Logowanie pomyślnej operacji
     */
    public function testGetInspectionsLogsSuccessfulOperation(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-15';
        $queryDto->endDate = '2025-10-22';
        $queryDto->createdByUserId = 1;
        $queryDto->page = 2;
        $queryDto->limit = 20;

        $mockUser = $this->createMockUser(1, 'Jan Kowalski');

        $repositoryResult = [
            'inspections' => [],
            'total' => 5,
        ];

        $this->userRepository->expects($this->once())->method('find')->willReturn($mockUser);
        $this->inspectionRepository->expects($this->once())->method('findWithFiltersAndPagination')->willReturn($repositoryResult);

        // Verify logger is called with correct parameters
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'Inspections list fetched',
                $this->callback(function ($context) {
                    return $context['filters']['startDate'] === '2025-10-15'
                        && $context['filters']['endDate'] === '2025-10-22'
                        && $context['filters']['createdByUserId'] === 1
                        && $context['pagination']['page'] === 2
                        && $context['pagination']['limit'] === 20
                        && $context['pagination']['total'] === 5;
                })
            );

        // Act
        $this->inspectionService->getInspections($queryDto);
    }

    /**
     * Test edge case - only start date without end date
     * TC-014: Tylko startDate bez endDate
     */
    public function testGetInspectionsOnlyStartDate(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-15';
        $queryDto->endDate = null;

        $repositoryResult = [
            'inspections' => [],
            'total' => 0,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->callback(function ($startDate) {
                    return $startDate instanceof DateTimeImmutable;
                }),
                $this->isNull(),
                $this->isNull(),
                1,
                50
            )
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
    }

    /**
     * Test edge case - only end date without start date
     * TC-015: Tylko endDate bez startDate
     */
    public function testGetInspectionsOnlyEndDate(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = null;
        $queryDto->endDate = '2025-10-22';

        $repositoryResult = [
            'inspections' => [],
            'total' => 0,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->isNull(),
                $this->callback(function ($endDate) {
                    return $endDate instanceof DateTimeImmutable;
                }),
                $this->isNull(),
                1,
                50
            )
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
    }

    /**
     * Test edge case - minimum limit (1)
     * TC-016: Limit = 1 (minimalna paginacja)
     */
    public function testGetInspectionsMinimumLimit(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 1;
        $queryDto->limit = 1;

        $repositoryResult = [
            'inspections' => [$this->createMockInspection(1)],
            'total' => 5,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertEquals(5, $result->meta->totalPages); // ceil(5/1) = 5
        $this->assertEquals(1, $result->meta->perPage);
    }

    /**
     * Test edge case - very large limit
     * TC-017: Bardzo duży limit
     */
    public function testGetInspectionsLargeLimit(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 1;
        $queryDto->limit = 100; // Max allowed by validation

        $repositoryResult = [
            'inspections' => array_map(fn($i) => $this->createMockInspection($i), range(1, 50)),
            'total' => 50,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertEquals(1, $result->meta->totalPages); // ceil(50/100) = 1
        $this->assertCount(50, $result->data);
    }

    /**
     * Test edge case - same start and end date
     * TC-018: Ta sama data rozpoczęcia i zakończenia
     */
    public function testGetInspectionsSameStartAndEndDate(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-15';
        $queryDto->endDate = '2025-10-15';

        $repositoryResult = [
            'inspections' => [$this->createMockInspection(1)],
            'total' => 1,
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->callback(function ($startDate) {
                    return $startDate instanceof DateTimeImmutable
                        && $startDate->format('Y-m-d H:i:s') === '2025-10-15 00:00:00';
                }),
                $this->callback(function ($endDate) {
                    return $endDate instanceof DateTimeImmutable
                        && $endDate->format('Y-m-d H:i:s') === '2025-10-15 23:59:59';
                }),
                $this->isNull(),
                1,
                50
            )
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertInstanceOf(InspectionListResponseDto::class, $result);
        $this->assertCount(1, $result->data);
    }

    /**
     * Test pagination calculation edge case - exact division
     */
    public function testPaginationCalculationExactDivision(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->page = 1;
        $queryDto->limit = 10;

        $repositoryResult = [
            'inspections' => array_map(fn($i) => $this->createMockInspection($i), range(1, 10)),
            'total' => 100, // Exactly divides by 10
        ];

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertEquals(10, $result->meta->totalPages); // ceil(100/10) = 10
    }

    /**
     * Test with all filters combined
     */
    public function testGetInspectionsWithAllFiltersCombined(): void
    {
        // Arrange
        $queryDto = new InspectionListQueryDto();
        $queryDto->startDate = '2025-10-01';
        $queryDto->endDate = '2025-10-31';
        $queryDto->createdByUserId = 5;
        $queryDto->page = 2;
        $queryDto->limit = 25;

        $mockUser = $this->createMockUser(5, 'Anna Nowak');
        $mockInspections = [$this->createMockInspection(1), $this->createMockInspection(2)];

        $repositoryResult = [
            'inspections' => $mockInspections,
            'total' => 30,
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($mockUser);

        $this->inspectionRepository
            ->expects($this->once())
            ->method('findWithFiltersAndPagination')
            ->with(
                $this->isInstanceOf(DateTimeImmutable::class),
                $this->isInstanceOf(DateTimeImmutable::class),
                5,
                2,
                25
            )
            ->willReturn($repositoryResult);

        $this->logger->expects($this->once())->method('info');

        // Act
        $result = $this->inspectionService->getInspections($queryDto);

        // Assert
        $this->assertCount(2, $result->data);
        $this->assertEquals(30, $result->meta->total);
        $this->assertEquals(2, $result->meta->currentPage);
        $this->assertEquals(2, $result->meta->totalPages); // ceil(30/25) = 2
    }

    /**
     * Helper method to create a mock Inspection entity
     *
     * @param int $id The inspection ID
     * @return Inspection&MockObject
     */
    private function createMockInspection(int $id): Inspection&MockObject
    {
        $inspection = $this->createMock(Inspection::class);
        $mockUser = $this->createMockUser($id, "User {$id}");

        $startDateTime = new DateTimeImmutable('2025-10-15 10:00:00');
        $endDateTime = new DateTimeImmutable('2025-10-15 10:30:00');
        $createdAt = new DateTimeImmutable('2025-10-01 08:00:00');

        $inspection->method('getId')->willReturn($id);
        $inspection->method('getStartDatetime')->willReturn($startDateTime);
        $inspection->method('getEndDatetime')->willReturn($endDateTime);
        $inspection->method('getVehicleMake')->willReturn('Toyota');
        $inspection->method('getVehicleModel')->willReturn('Corolla');
        $inspection->method('getLicensePlate')->willReturn("ABC{$id}234");
        $inspection->method('getClientName')->willReturn("Jan Kowalski {$id}");
        $inspection->method('getPhoneNumber')->willReturn('123456789');
        $inspection->method('getCreatedByUser')->willReturn($mockUser);
        $inspection->method('getCreatedAt')->willReturn($createdAt);
        $inspection->method('isPast')->willReturn(false);

        return $inspection;
    }

    /**
     * Helper method to create a mock User entity
     *
     * @param int $id The user ID
     * @param string $name The user name
     * @return User&MockObject
     */
    private function createMockUser(int $id, string $name): User&MockObject
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getName')->willReturn($name);
        $user->method('getUsername')->willReturn("user{$id}");

        return $user;
    }
}

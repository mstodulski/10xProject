<?php

namespace App\Dto;

use App\Entity\Inspection;

class InspectionResponseDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $startDatetime,
        public readonly string $endDatetime,
        public readonly string $vehicleMake,
        public readonly string $vehicleModel,
        public readonly string $licensePlate,
        public readonly string $clientName,
        public readonly string $phoneNumber,
        public readonly UserBasicDto $createdByUser,
        public readonly string $createdAt,
        public readonly bool $isPast
    ) {}

    public static function fromEntity(Inspection $inspection): self
    {
        return new self(
            id: $inspection->getId(),
            startDatetime: $inspection->getStartDatetime()->format('c'), // ISO 8601
            endDatetime: $inspection->getEndDatetime()->format('c'),
            vehicleMake: $inspection->getVehicleMake(),
            vehicleModel: $inspection->getVehicleModel(),
            licensePlate: $inspection->getLicensePlate(),
            clientName: $inspection->getClientName(),
            phoneNumber: $inspection->getPhoneNumber(),
            createdByUser: UserBasicDto::fromEntity($inspection->getCreatedByUser()),
            createdAt: $inspection->getCreatedAt()->format('c'),
            isPast: $inspection->isPast()
        );
    }
}

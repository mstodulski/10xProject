<?php

namespace App\Entity;

use App\Repository\InspectionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InspectionRepository::class)]
#[ORM\Table(name: 'inspections')]
#[ORM\Index(columns: ['start_datetime'], name: 'idx_start_datetime')]
#[ORM\Index(columns: ['end_datetime'], name: 'idx_end_datetime')]
#[ORM\Index(columns: ['created_by_user_id'], name: 'idx_created_by_user_id')]
class Inspection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDatetime;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $endDatetime;

    #[ORM\Column(type: 'string', length: 64)]
    private string $vehicleMake;

    #[ORM\Column(type: 'string', length: 64)]
    private string $vehicleModel;

    #[ORM\Column(type: 'string', length: 20)]
    private string $licensePlate;

    #[ORM\Column(type: 'string', length: 64)]
    private string $clientName;

    #[ORM\Column(type: 'string', length: 20)]
    private string $phoneNumber;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_user_id', nullable: false)]
    private User $createdByUser;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDatetime(): DateTimeImmutable
    {
        return $this->startDatetime;
    }

    public function setStartDatetime(DateTimeImmutable $startDatetime): self
    {
        $this->startDatetime = $startDatetime;
        return $this;
    }

    public function getEndDatetime(): DateTimeImmutable
    {
        return $this->endDatetime;
    }

    public function setEndDatetime(DateTimeImmutable $endDatetime): self
    {
        $this->endDatetime = $endDatetime;
        return $this;
    }

    public function getVehicleMake(): string
    {
        return $this->vehicleMake;
    }

    public function setVehicleMake(string $vehicleMake): self
    {
        $this->vehicleMake = $vehicleMake;
        return $this;
    }

    public function getVehicleModel(): string
    {
        return $this->vehicleModel;
    }

    public function setVehicleModel(string $vehicleModel): self
    {
        $this->vehicleModel = $vehicleModel;
        return $this;
    }

    public function getLicensePlate(): string
    {
        return $this->licensePlate;
    }

    public function setLicensePlate(string $licensePlate): self
    {
        $this->licensePlate = $licensePlate;
        return $this;
    }

    public function getClientName(): string
    {
        return $this->clientName;
    }

    public function setClientName(string $clientName): self
    {
        $this->clientName = $clientName;
        return $this;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getCreatedByUser(): User
    {
        return $this->createdByUser;
    }

    public function setCreatedByUser(User $createdByUser): self
    {
        $this->createdByUser = $createdByUser;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Check if this inspection is in the past
     */
    public function isPast(): bool
    {
        return $this->startDatetime < new DateTimeImmutable();
    }

    /**
     * Check if this inspection is in the future
     */
    public function isFuture(): bool
    {
        return $this->startDatetime > new DateTimeImmutable();
    }

    /**
     * Check if this inspection is happening today
     */
    public function isToday(): bool
    {
        $now = new DateTimeImmutable();
        return $this->startDatetime->format('Y-m-d') === $now->format('Y-m-d');
    }

    /**
     * Get the duration of the inspection in minutes
     */
    public function getDurationInMinutes(): int
    {
        return (int) (($this->endDatetime->getTimestamp() - $this->startDatetime->getTimestamp()) / 60);
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDate(): string
    {
        return $this->startDatetime->format('Y-m-d');
    }

    /**
     * Get formatted start time for display
     */
    public function getFormattedStartTime(): string
    {
        return $this->startDatetime->format('H:i');
    }

    /**
     * Get formatted end time for display
     */
    public function getFormattedEndTime(): string
    {
        return $this->endDatetime->format('H:i');
    }

    /**
     * Get formatted date and time range for display
     */
    public function getFormattedDateTimeRange(): string
    {
        return sprintf(
            '%s %s - %s',
            $this->getFormattedDate(),
            $this->getFormattedStartTime(),
            $this->getFormattedEndTime()
        );
    }

    /**
     * Get full vehicle description
     */
    public function getVehicleDescription(): string
    {
        return sprintf('%s %s (%s)', $this->vehicleMake, $this->vehicleModel, $this->licensePlate);
    }
}

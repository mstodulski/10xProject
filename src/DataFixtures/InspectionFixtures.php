<?php

namespace App\DataFixtures;

use App\Entity\Inspection;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures for Inspection entities
 *
 * Generates inspections for demo purposes:
 * - Time range: -12 days to +7 days from fixture load date (which should be Wednesday)
 * - Only weekdays (Monday-Friday)
 * - 2-5 inspections per day
 * - Random time slots between 07:00-15:30 (last possible start time)
 * - Each inspection lasts 30 minutes
 * - 15-minute buffer between inspections
 * - Random Polish vehicle and client data
 * - Evenly distributed among 4 consultants
 *
 * NOTE: Fixtures are designed to be loaded on Wednesdays for demo site refresh
 */
class InspectionFixtures extends Fixture implements DependentFixtureInterface
{
    private const INSPECTION_DURATION_MINUTES = 30;
    private const BUFFER_MINUTES = 15;
    private const MIN_INSPECTIONS_PER_DAY = 2;
    private const MAX_INSPECTIONS_PER_DAY = 5;
    private const DAYS_BACK = 12;
    private const DAYS_FORWARD = 7;

    // Available time slots (07:00 to 15:30 in 15-minute increments)
    private const AVAILABLE_SLOTS = [
        '07:00', '07:15', '07:30', '07:45',
        '08:00', '08:15', '08:30', '08:45',
        '09:00', '09:15', '09:30', '09:45',
        '10:00', '10:15', '10:30', '10:45',
        '11:00', '11:15', '11:30', '11:45',
        '12:00', '12:15', '12:30', '12:45',
        '13:00', '13:15', '13:30', '13:45',
        '14:00', '14:15', '14:30', '14:45',
        '15:00', '15:15', '15:30'
    ];

    public function load(ObjectManager $manager): void
    {
        // Get all consultants
        $consultants = [
            $this->getReference(UserFixtures::CONSULTANT_1_REFERENCE, User::class),
            $this->getReference(UserFixtures::CONSULTANT_2_REFERENCE, User::class),
            $this->getReference(UserFixtures::CONSULTANT_3_REFERENCE, User::class),
            $this->getReference(UserFixtures::CONSULTANT_4_REFERENCE, User::class),
        ];

        $now = new DateTimeImmutable();
        $startDate = $now->modify('-' . self::DAYS_BACK . ' days');
        $endDate = $now->modify('+' . self::DAYS_FORWARD . ' days');

        $currentDate = $startDate;
        $consultantIndex = 0;

        // Generate inspections for each day in range
        while ($currentDate <= $endDate) {
            // Skip weekends (Saturday = 6, Sunday = 7)
            $dayOfWeek = (int) $currentDate->format('N');
            if ($dayOfWeek === 6 || $dayOfWeek === 7) {
                $currentDate = $currentDate->modify('+1 day');
                continue;
            }

            // Random number of inspections for this day
            $inspectionsCount = random_int(
                self::MIN_INSPECTIONS_PER_DAY,
                self::MAX_INSPECTIONS_PER_DAY
            );

            // Get random time slots for this day (without overlaps)
            $daySlots = $this->getRandomTimeSlots($inspectionsCount);

            // Create inspections for this day
            foreach ($daySlots as $timeSlot) {
                $inspection = $this->createInspection(
                    $currentDate,
                    $timeSlot,
                    $consultants[$consultantIndex]
                );

                $manager->persist($inspection);

                // Rotate consultant for even distribution
                $consultantIndex = ($consultantIndex + 1) % count($consultants);
            }

            $currentDate = $currentDate->modify('+1 day');
        }

        $manager->flush();
    }

    /**
     * Get random time slots ensuring no overlaps (with 15-min buffer)
     */
    private function getRandomTimeSlots(int $count): array
    {
        $availableSlots = self::AVAILABLE_SLOTS;
        $selectedSlots = [];

        for ($i = 0; $i < $count && count($availableSlots) > 0; $i++) {
            // Pick random slot
            $randomIndex = array_rand($availableSlots);
            $selectedSlot = $availableSlots[$randomIndex];
            $selectedSlots[] = $selectedSlot;

            // Remove this slot and adjacent slots to prevent overlaps
            $availableSlots = $this->removeSlotWithBuffer($availableSlots, $selectedSlot);
        }

        // Sort slots chronologically
        sort($selectedSlots);

        return $selectedSlots;
    }

    /**
     * Remove a time slot and adjacent slots (for 30min + 15min buffer)
     */
    private function removeSlotWithBuffer(array $slots, string $selectedSlot): array
    {
        $slotIndex = array_search($selectedSlot, self::AVAILABLE_SLOTS);
        $slotsToRemove = [];

        // Remove selected slot and 3 slots after it (30min inspection + 15min buffer = 45min = 3 slots)
        for ($i = 0; $i <= 3; $i++) {
            if (isset(self::AVAILABLE_SLOTS[$slotIndex + $i])) {
                $slotsToRemove[] = self::AVAILABLE_SLOTS[$slotIndex + $i];
            }
        }

        return array_values(array_diff($slots, $slotsToRemove));
    }

    /**
     * Create a single inspection
     */
    private function createInspection(
        DateTimeImmutable $date,
        string $timeSlot,
        User $consultant
    ): Inspection {
        // Parse time slot
        [$hour, $minute] = explode(':', $timeSlot);
        $startDateTime = $date->setTime((int) $hour, (int) $minute, 0);
        $endDateTime = $startDateTime->modify('+' . self::INSPECTION_DURATION_MINUTES . ' minutes');

        // Generate random vehicle and client data
        $car = PolishDataGenerator::getRandomCar();

        $inspection = new Inspection();
        $inspection->setStartDatetime($startDateTime);
        $inspection->setEndDatetime($endDateTime);
        $inspection->setVehicleMake($car['make']);
        $inspection->setVehicleModel($car['model']);
        $inspection->setLicensePlate(PolishDataGenerator::getRandomLicensePlate());
        $inspection->setClientName(PolishDataGenerator::getRandomName());
        $inspection->setPhoneNumber(PolishDataGenerator::getRandomPhoneNumber());
        $inspection->setCreatedByUser($consultant);

        return $inspection;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}

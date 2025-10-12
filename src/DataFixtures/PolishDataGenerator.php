<?php

namespace App\DataFixtures;

/**
 * Helper class for generating Polish-specific fake data
 */
class PolishDataGenerator
{
    private const FIRST_NAMES = [
        'Jan', 'Piotr', 'Krzysztof', 'Andrzej', 'Tomasz', 'Paweł', 'Michał', 'Marcin',
        'Anna', 'Maria', 'Katarzyna', 'Małgorzata', 'Joanna', 'Ewa', 'Barbara', 'Agnieszka'
    ];

    private const LAST_NAMES = [
        'Kowalski', 'Nowak', 'Wiśniewski', 'Wójcik', 'Kowalczyk', 'Kamiński', 'Lewandowski',
        'Zieliński', 'Szymański', 'Woźniak', 'Dąbrowski', 'Kozłowski', 'Jankowski', 'Mazur'
    ];

    private const CAR_MAKES = [
        'Toyota', 'Volkswagen', 'Ford', 'Opel', 'Renault', 'Peugeot', 'Skoda', 'Audi',
        'BMW', 'Mercedes', 'Fiat', 'Nissan', 'Hyundai', 'Kia', 'Mazda', 'Honda'
    ];

    private const CAR_MODELS = [
        'Corolla', 'Golf', 'Focus', 'Astra', 'Clio', '308', 'Octavia', 'A4',
        'Seria 3', 'C-Class', '500', 'Qashqai', 'i30', 'Ceed', 'CX-5', 'Civic'
    ];

    private const PLATE_LETTERS = [
        'WA', 'KR', 'PO', 'WR', 'GD', 'LU', 'BI', 'SZ', 'KT', 'LD', 'RZ', 'BY', 'OP', 'GZ'
    ];

    /**
     * Generate random Polish full name
     */
    public static function getRandomName(): string
    {
        $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
        $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];

        return $firstName . ' ' . $lastName;
    }

    /**
     * Generate random Polish phone number (format: +48 XXX XXX XXX)
     */
    public static function getRandomPhoneNumber(): string
    {
        $part1 = str_pad((string) random_int(500, 799), 3, '0', STR_PAD_LEFT);
        $part2 = str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT);
        $part3 = str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT);

        return "+48 {$part1} {$part2} {$part3}";
    }

    /**
     * Generate random Polish license plate (format: XX12345 or XX1234A)
     */
    public static function getRandomLicensePlate(): string
    {
        $letters = self::PLATE_LETTERS[array_rand(self::PLATE_LETTERS)];
        $numbers = str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);

        // Randomly add a letter at the end (newer format)
        if (random_int(0, 1) === 1) {
            $endLetter = chr(random_int(65, 90)); // A-Z
            return $letters . substr($numbers, 0, 4) . $endLetter;
        }

        return $letters . $numbers;
    }

    /**
     * Generate random car make
     */
    public static function getRandomCarMake(): string
    {
        return self::CAR_MAKES[array_rand(self::CAR_MAKES)];
    }

    /**
     * Generate random car model
     */
    public static function getRandomCarModel(): string
    {
        return self::CAR_MODELS[array_rand(self::CAR_MODELS)];
    }

    /**
     * Generate random car (make and model pair)
     * Returns array with 'make' and 'model' keys
     */
    public static function getRandomCar(): array
    {
        return [
            'make' => self::getRandomCarMake(),
            'model' => self::getRandomCarModel(),
        ];
    }
}

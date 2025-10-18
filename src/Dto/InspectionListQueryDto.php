<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class InspectionListQueryDto
{
    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
    )]
    public ?string $startDate = null;

    #[Assert\Regex(
        pattern: '/^\d{4}-\d{2}-\d{2}$/',
        message: 'Nieprawidłowy format daty. Użyj formatu YYYY-MM-DD'
    )]
    public ?string $endDate = null;

    #[Assert\Positive(message: 'Parametr page musi być liczbą całkowitą większą od 0')]
    public int $page = 1;

    #[Assert\Range(
        min: 1,
        max: 100,
        notInRangeMessage: 'Parametr limit musi być w zakresie od {{ min }} do {{ max }}'
    )]
    public int $limit = 50;

    #[Assert\Positive(message: 'Parametr createdByUserId musi być liczbą całkowitą większą od 0')]
    public ?int $createdByUserId = null;
}

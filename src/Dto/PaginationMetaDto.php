<?php

namespace App\Dto;

class PaginationMetaDto
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages
    ) {}
}

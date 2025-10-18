<?php

namespace App\Dto;

class InspectionListResponseDto
{
    /**
     * @param InspectionResponseDto[] $data
     */
    public function __construct(
        public readonly array $data,
        public readonly PaginationMetaDto $meta
    ) {}
}

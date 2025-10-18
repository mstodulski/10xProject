<?php

namespace App\Dto;

use App\Entity\User;

class UserBasicDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->getId(),
            name: $user->getName()
        );
    }
}

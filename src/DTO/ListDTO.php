<?php

declare(strict_types=1);

namespace RestfulBundle\DTO;

class ListDTO
{
    public function __construct(
        public int $total,
        public array $items
    ) {}
}

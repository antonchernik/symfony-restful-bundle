<?php

declare(strict_types=1);

namespace RestfulBundle\DTO;

class ListDTO
{
    public int $total;
    public array $items;

    public function __construct(int $total, array $items)
    {
        $this->total = $total;
        $this->items = $items;
    }
}

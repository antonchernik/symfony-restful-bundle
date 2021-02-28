<?php

declare(strict_types=1);

namespace RestfulBundle\Dto;

class ListDto
{
    public int $total;
    public array $items;

    public function __construct(int $total, array $items)
    {
        $this->total = $total;
        $this->items = $items;
    }
}

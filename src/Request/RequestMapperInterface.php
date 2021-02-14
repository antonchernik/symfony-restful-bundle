<?php

declare(strict_types=1);

namespace RestfulBundle\Request;

use Symfony\Component\HttpFoundation\Request;

interface RequestMapperInterface
{
    public function getRequestDTO(Request $request): string;
}
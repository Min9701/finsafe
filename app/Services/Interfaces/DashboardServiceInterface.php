<?php

namespace App\Services\Interfaces;

interface DashboardServiceInterface
{
    public function getDashboardData(int $userId): array;
}

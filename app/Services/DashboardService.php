<?php

declare(strict_types=1);

final class DashboardService
{
    public function __construct(private readonly DashboardRepository $repository) {}

    public function summary(int $userId): array
    {
        return [
            'certificates' => $this->repository->count('certificados', "estado = 'emitido'"),
            'pending' => $this->repository->count('certificados', "estado = 'borrador'"),
            'printed' => $this->repository->count('impresiones_certificado'),
            'templates' => $this->repository->count('plantillas', 'activo = 1'),
            'recent' => $this->repository->recentCertificates(),
            'monthly' => $this->repository->monthlyEmissions(),
            'statuses' => $this->repository->statusSummary(),
            'trends' => $this->repository->monthlyTrends(),
            'notifications' => $this->repository->unreadNotifications($userId),
        ];
    }
}

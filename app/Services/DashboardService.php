<?php

declare(strict_types=1);

final class DashboardService
{
    public function __construct(private readonly DashboardRepository $repository) {}

    public function summary(int $userId): array
    {
        return [
            'certificates' => $this->repository->count('certificados', "estado = 'emitido' AND deleted_at IS NULL"),
            'pending' => $this->repository->count('certificados', "estado = 'borrador' AND deleted_at IS NULL"),
            'printed' => $this->repository->count('impresiones_certificado', 'certificado_id IN (SELECT id FROM certificados WHERE estado = \'emitido\' AND deleted_at IS NULL)'),
            'templates' => $this->repository->count('plantillas', 'activo = 1 AND deleted_at IS NULL'),
            'recent' => $this->repository->recentCertificates(),
            'monthly' => $this->repository->monthlyEmissions(),
            'statuses' => $this->repository->statusSummary(),
            'trends' => $this->repository->monthlyTrends(),
            'notifications' => $this->repository->unreadNotifications($userId),
            'notification_items' => $this->repository->notifications($userId),
        ];
    }
}

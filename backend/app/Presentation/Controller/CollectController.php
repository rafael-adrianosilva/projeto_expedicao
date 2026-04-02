<?php

namespace App\Presentation\Controller;

use App\Application\Service\CollectItemService;
use App\Application\Service\ReportService;
use App\Domain\Repository\CollectedItemRepository;

class CollectController
{
    private CollectedItemRepository $repository;
    private CollectItemService $collectService;
    private ReportService $reportService;

    public function __construct(
        CollectedItemRepository $repository,
        CollectItemService $collectService,
        ReportService $reportService
    ) {
        $this->repository = $repository;
        $this->collectService = $collectService;
        $this->reportService = $reportService;
    }

    public function listItems(): void
    {
        $items = $this->repository->findAll();
        $this->jsonResponse(array_map(fn($item) => $item->toArray(), $items));
    }

    public function collectItem(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $code = $data['code'] ?? '';

        try {
            $item = $this->collectService->execute($code);
            $this->jsonResponse($item->toArray(), 201);
        } catch (\InvalidArgumentException $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 409);
        }
    }

    public function sendReport(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? 'elsalvadorrafa3@gmail.com';
        $finalEmail = $data['finalEmail'] ?? 'elsalvadorrafa3@gmail.com';
        $destination = $data['destination'] ?? 'Não informado';
        $finalDestination = $data['finalDestination'] ?? 'Não informado';

        try {
            $this->reportService->sendReport($email, $finalEmail, $destination, $finalDestination);
            $this->jsonResponse(['success' => 'Relatórios enviados com sucesso.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function clearAll(): void
    {
        $this->repository->deleteAll();
        $this->jsonResponse(['success' => 'Todos os itens foram removidos.']);
    }

    public function deleteItem(int $id): void
    {
        $this->repository->deleteById($id);
        $this->jsonResponse(['success' => 'Item removido com sucesso.']);
    }

    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}

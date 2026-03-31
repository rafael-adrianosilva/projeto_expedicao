<?php

namespace App\Application\Service;

use App\Domain\Repository\CollectedItemRepository;
use App\Infrastructure\Email\EmailSender;

class ReportService
{
    private CollectedItemRepository $repository;
    private EmailSender $emailSender;

    public function __construct(CollectedItemRepository $repository, EmailSender $emailSender)
    {
        $this->repository = $repository;
        $this->emailSender = $emailSender;
    }

    public function sendReport(string $email): void
    {
        $items = $this->repository->findAll();

        if (empty($items)) {
            throw new \Exception("Nenhum item recolhido para enviar.");
        }

        $body = "Relatório de Coleta de Dados - Fachini Logística\n\n";
        $body .= "Data: " . date('d/m/Y H:i:s') . "\n";
        $body .= "Total de volumes: " . count($items) . "\n\n";
        $body .= "===================================\n";
        $body .= "CÓDIGO\t\t\tDATA/HORA\n";
        $body .= "===================================\n";

        foreach ($items as $item) {
            $body .= $item->getCode() . "\t\t" . $item->getTimestamp()->format('d/m/Y H:i:s') . "\n";
        }

        // ABAIXO O SISTEMA ENVIA PARA O GMAIL VINCULADO À FILIAL SELECIONADA
        try {
            $this->emailSender->send(
                $email,
                'Coleta de Dados - ' . date('d/m/Y'),
                $body
            );
        } catch (\Exception $e) {
            // Se falhar o primeiro, ainda tentamos o de teste?
            // Para garantir que pelo menos o de teste receba se o da filial estiver errado.
            error_log("Erro ao enviar para a filial: " . $e->getMessage());
        }

        // ENVIO PARA O EMAIL DE TESTE (CONFORME SOLICITADO)
        $this->emailSender->send(
<<<<<<< HEAD
            'rafael.adriano2801@gmail.com',
=======
            'joao.p.pereira73@aluno.senai.br',
>>>>>>> 776fd00eec6424850e0467eb69534fb8d915bae9
            '[TESTE] Coleta de Dados - ' . date('d/m/Y'),
            $body
        );

        $this->repository->deleteAll();
    }
}
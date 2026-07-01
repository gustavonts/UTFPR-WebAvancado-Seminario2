<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendStatusUpdate(string $to, string $status, int $pedidoId): void
    {
        $email = (new Email())
            ->from('noreply@ecommerce.com')
            ->to($to)
            ->subject('Atualização do seu pedido #' . $pedidoId)
            ->html("
                <h2>Status do pedido atualizado</h2>
                <p>Seu pedido #$pedidoId mudou para: <strong>$status</strong></p>
            ");

        $this->mailer->send($email);
    }
}
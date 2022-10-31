<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{
    public function notificaTerminoLeilao(Leilao $leilao)
    {
        $success = mail(
            'user@email.com',
            'Leilão finalizado',
            "Leilão para {$leilao->recuperarDescricao()} finalizado."
        );

        if (!$success) {
            throw new \DomainException('Erro ao enviar e-mail.');
        }
    }
}

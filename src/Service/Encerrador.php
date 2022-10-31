<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;

class Encerrador
{
    private LeilaoDao $dao;
    private EnviadorEmail $enviador;

    public function __construct(LeilaoDao $dao, EnviadorEmail $enviador)
    {
        $this->dao = $dao;
        $this->enviador = $enviador;
    }

    public function encerra(): void
    {
        $leiloes = $this->dao->recuperarNaoFinalizados();

        foreach ($leiloes as $leilao) {
            if ($leilao->temMaisDeUmaSemana()) {
                try {
                    $leilao->finaliza();
                    $this->dao->atualiza($leilao);
                    $this->enviador->notificaTerminoLeilao($leilao);
                } catch (\DomainException $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }
}

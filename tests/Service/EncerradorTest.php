<?php

namespace Service;

use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class LeilaoDaoMock extends LeilaoDao
{
    private array $leiloes = [];

    public function salva(Leilao $leilao): void
    {
        $this->leiloes[] = $leilao;
    }

    public function recuperarNaoFinalizados(): array
    {
        return array_filter(
            $this->leiloes,
            fn(Leilao $leilao) => !$leilao->estaFinalizado()
        );
    }

    public function recuperarFinalizados(): array
    {
        return array_filter(
            $this->leiloes,
            fn(Leilao $leilao) => $leilao->estaFinalizado()
        );
    }

    public function atualiza(Leilao $leilao)
    {
    }
}

class EncerradorTest extends TestCase
{
    public function test_deve_encerrar_leiloes_com_mais_de_uma_semana()
    {
        $leilaoFiat = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $leilaoVariant = new Leilao('Variant 1973 0Km', new \DateTimeImmutable('10 days ago'));

        $leilaoDaoMock = new LeilaoDaoMock();
        $leilaoDaoMock->salva($leilaoFiat);
        $leilaoDaoMock->salva($leilaoVariant);

        $encerrador = new Encerrador($leilaoDaoMock);
        $encerrador->encerra();

        $leiloesEncerrados = $leilaoDaoMock->recuperarFinalizados();
        $this->assertCount(2, $leiloesEncerrados);
        $this->assertEquals(
            'Fiat 147 0Km',
            $leiloesEncerrados[0]->recuperarDescricao()
        );
        $this->assertEquals(
            'Variant 1973 0Km',
            $leiloesEncerrados[1]->recuperarDescricao()
        );
    }
}

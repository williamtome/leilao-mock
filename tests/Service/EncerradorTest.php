<?php

namespace Service;

use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    public function test_deve_encerrar_leiloes_com_mais_de_uma_semana()
    {
        $leilaoFiat = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
        $leilaoVariant = new Leilao('Variant 1973 0Km', new \DateTimeImmutable('10 days ago'));
        $leilaoDaoMock = $this->createMock(LeilaoDao::class);
        $leilaoDaoMock->method('recuperarNaoFinalizados')
            ->willReturn([$leilaoFiat, $leilaoVariant]);
        $leilaoDaoMock->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive([$leilaoFiat], [$leilaoVariant]);
        $leilaoDaoMock->method('recuperarFinalizados')
            ->willReturn([$leilaoFiat, $leilaoVariant]);

        $encerrador = new Encerrador($leilaoDaoMock);
        $encerrador->encerra();

        $leiloes = [$leilaoFiat, $leilaoVariant];
        $this->assertCount(2, $leiloes);
        $this->assertTrue($leiloes[0]->estaFinalizado());
        $this->assertTrue($leiloes[1]->estaFinalizado());
    }
}

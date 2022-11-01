<?php

namespace Service;

use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    private Encerrador $encerrador;
    private MockObject $enviadorDeEmailMock;
    private $leilaoFiat;
    private $leilaoVariant;

    protected function setUp(): void
    {
        $this->leilaoFiat = new Leilao(
            'Fiat 147 0Km',
            new \DateTimeImmutable('8 days ago')
        );
        $this->leilaoVariant = new Leilao(
            'Variant 1973 0Km',
            new \DateTimeImmutable('10 days ago')
        );
        $leilaoDaoMock = $this->createMock(LeilaoDao::class);
        $leilaoDaoMock->method('recuperarNaoFinalizados')
            ->willReturn([
                $this->leilaoFiat,
                $this->leilaoVariant
            ]);
        $leilaoDaoMock->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->leilaoFiat],
                [$this->leilaoVariant]
            );
        $leilaoDaoMock->method('recuperarFinalizados')
            ->willReturn([
                $this->leilaoFiat,
                $this->leilaoVariant
            ]);
        $this->enviadorDeEmailMock = $this->createMock(EnviadorEmail::class);
        $this->encerrador = new Encerrador($leilaoDaoMock, $this->enviadorDeEmailMock);
    }

    public function test_deve_encerrar_leiloes_com_mais_de_uma_semana()
    {
        $this->encerrador->encerra();
        $leiloes = [$this->leilaoFiat, $this->leilaoVariant];
        $this->assertCount(2, $leiloes);
        $this->assertTrue($leiloes[0]->estaFinalizado());
        $this->assertTrue($leiloes[1]->estaFinalizado());
    }

    public function test_deve_continuar_o_processo_ao_encontrar_erro_ao_enviar_email()
    {
        $exception = new \DomainException('Erro ao enviar e-mail.');
        $this->enviadorDeEmailMock->expects($this->exactly(2))
            ->method('notificaTerminoLeilao')
            ->willThrowException($exception);
        $this->encerrador->encerra();
    }

    public function test_deve_enviar_leilao_por_email_apos_finalizado()
    {
        $this->enviadorDeEmailMock->expects($this->exactly(2))
            ->method('notificaTerminoLeilao')
            ->willReturnCallback(function (Leilao $leilao) {
                $this->assertTrue($leilao->estaFinalizado());
            });

        $this->encerrador->encerra();
    }
}

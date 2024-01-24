<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco sub cabecalho com a identificação do CFe, do cliente e do ambiente de homologação
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoII
{
  protected function blocoII($y, $minimalist = false)
  {

    $nCFe = $this->getTagValue($this->ide, "nCFe");
    $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];

    if (!$minimalist) {

      $texto = "Extrato Nº {$nCFe}\n"
        . "CUPOM FISCAL ELETRÔNICO - SAT";

      $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];

      $y += (float) $this->pdf->textBox(
        $this->margem,
        $y - 2,
        $this->wPrint,
        $this->bloco2H,
        $texto,
        $aFont,
        'C',
        'C',
        false,
        '',
        true
      );

      if ($this->tpAmb == 2) {
        $texto = "= TESTE =\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>";

        $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];

        $y += (float) $this->pdf->textBox(
          $this->margem,
          $y - 4,
          $this->wPrint,
          $this->bloco2H,
          $texto,
          $aFont,
          'C',
          'C',
          false,
          '',
          true
        );
      }

      $this->pdf->dashedHLine($this->margem, $y, $this->wPrint, 0.1, 30);
    } else {
      $y += 2;
    }

    $offset = 0;

    if ($this->canceled) {
      $texto = "DADOS DO CUPOM FISCAL ELETRÔNICO CANCELADO";
      $y += (float) $this->pdf->textBox($this->margem, $y - 4, $this->wPrint, $this->bloco2H, $texto, $aFont, 'C', 'C', false, '', true);
      $this->pdf->dashedHLine($this->margem, $y - 2, $this->wPrint, 0.1, 30);
    }

    $cpf = $this->getTagValue($this->dest, "CPF");
    $cnpj = $this->getTagValue($this->dest, "CNPJ");

    if ($cpf || $cnpj) {

      $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => ''];

      if ($cpf) {
        $cpf = $this->formatField($cpf, "###.###.###-##");
        $texto = "CPF do consumidor: {$cpf}";
      }

      //
      else {
        $cnpj = $this->formatField($cnpj, "###.###.###/####-##");
        $texto = "CNPJ do consumidor: {$cnpj}";
      }

      $y += (float) $this->pdf->textBox($this->margem, $y - 4, $this->wPrint, $this->bloco2H, $texto, $aFont, 'C', 'L', false, '', true);

      $nome = $this->getTagValue($this->dest, "xNome");

      if ($cpf) {
        $texto = "Nome: {$nome}";
      }
      //
      else {
        $texto = "Razão social: {$nome}";
      }

      if (!$this->canceled) {
        $y += (float) $this->pdf->textBox($this->margem, $y - 8, $this->wPrint, $this->bloco2H, $texto, $aFont, 'C', 'L', false, '', true);

        if (!$minimalist) {
          $this->pdf->dashedHLine($this->margem, $y - 6, $this->wPrint, 0.1, 30);
        }
      }

      //
      else {
        $offset = 4;
      }
    }

    // se não tiver cliente
    else {
      $y += 6;

      if ($this->canceled) {
        $y += 2;
      }
    }

    return $y + $offset;
  }
}

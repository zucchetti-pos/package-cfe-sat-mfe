<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco formas de pagamento
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoV
{
  protected function blocoV($y)
  {
    $this->bloco5H = $this->calculateHeightPag();

    $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];

    $arpgto = [];

    if ($this->pag && $this->pag->length > 0) {
      foreach ($this->pag as $pgto) {
        $tipo = $this->pagType((int) $this->getTagValue($pgto, 'cMP'));
        $valor = number_format((float) $this->getTagValue($pgto, 'vMP'), 2, ',', '.');
        $arpgto[] = [
          'tipo' => $tipo,
          'valor' => $valor
        ];
      }
    }

    $y -= 8;

    $z = $y;

    foreach ($arpgto as $p) {
      $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $p['tipo'], $aFont, 'T', 'L', false, '', false);
      $y2 = (float) $this->pdf->textBox(
        $this->margem,
        $z,
        $this->wPrint,
        3,
        $p['valor'],
        $aFont,
        'T',
        'R',
        false,
        '',
        false
      );

      $z += $y2;
    }

    if (!$this->canceled) {

      $texto = "Troco R$";
      $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $texto, $aFont, 'T', 'L', false, '', false);
      $texto =  !empty($this->vTroco) ? number_format((float) $this->vTroco, 2, ',', '.') : '0,00';
      $z += (float) $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $texto, $aFont, 'T', 'R', false, '', false);

      $y += 4;
    }

    //
    else {
      $y -= 4;
    }

    if (count($this->obsFisco ?: [])) {
      $z += 1;
    }

    foreach ($this->obsFisco ?: [] as $obs) {
      $xCampo = $obs->getAttribute("xCampo");
      $valor = $this->getTagValue($obs, 'xTexto');
      $texto = "{$xCampo}: {$valor}";

      $z += (float) $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $texto, $aFont, 'T', 'L', false, '', false);
    }

    if (!$this->canceled) {
      $z += 1;
      $this->pdf->dashedHLine($this->margem, $z, $this->wPrint, 0.1, 30);
    }

    $xLgr = $this->getTagValue($this->entrega, 'xLgr');

    if ($this->entrega && $xLgr) {

      $z += (float) $this->pdf->textBox($this->margem, $z + 1, $this->wPrint, 3, 'DADOS PARA ENTREGA', $aFont, 'T', 'L', false, '', false);

      $nro = $this->getTagValue($this->entrega, 'nro');
      $xBairro = $this->getTagValue($this->entrega, 'xBairro');
      $xMun = $this->getTagValue($this->entrega, 'xMun');
      $UF = $this->getTagValue($this->entrega, 'UF');

      $nro = (', ' . $nro ?: 'S/N');
      $xBairro = !empty($xBairro) ? (', ' . $xBairro) : ' ';
      $xMun = !empty($xMun) ? (', ' . $xMun) : ' ';
      $UF = !empty($UF) ? (' - ' . $UF) : '';

      $texto = "{$xLgr}{$nro}{$xBairro}{$xMun}{$UF}";

      $z += (float) $this->pdf->textBox($this->margem, $z + 1, $this->wPrint, 3, $texto, $aFont, 'T', 'L', false, '', false);

      $z += 2;

      $this->pdf->dashedHLine($this->margem, $z, $this->wPrint, 0.1, 30);
    }

    //
    else {
      $y += 4;
    }

    $offset = 0;

    if ($this->infAdic) {

      $z += (float) $this->pdf->textBox($this->margem, $z + 2, $this->wPrint, 3, 'OBSERVAÇÕES DO CONTRIBUINTE', $aFont, 'T', 'L', false, '', false);
      $z += (float) $this->pdf->textBox($this->margem, $z + 2, $this->wPrint, 6, $this->getTagValue($this->infAdic, 'infCpl'), $aFont, 'T', 'L', false, '', false);
    }

    if (!$this->canceled) {

      $totaisTrib = number_format($this->getTagValue($this->total, 'vCFeLei12741') ?: '0.00', 2, ',', '.');

      $texto = "Valor total aproximado dos tributos deste cupom*\n(conforme Lei Fed. 12.741/2012)";
      $z += (float) $this->pdf->textBox($this->margem, $z + 3, $this->wPrint, 3, $texto, $aFont, 'T', 'L', false, '', false);
      $z += (float) $this->pdf->textBox($this->margem, $z, $this->wPrint, 3, $totaisTrib, $aFont, 'T', 'R', false, '', false);

      $z += 5;
      $offset += 2;
      $this->pdf->dashedHLine($this->margem, $z, $this->wPrint, 0.1, 30);
    }

    return $z + $offset;
  }

  protected function pagType($type)
  {
    $lista = [
      1 => 'Dinheiro',
      2 => 'Cheque',
      3 => 'Cartão de Crédito',
      4 => 'Cartão de Débito',
      5 => 'Crédito Loja',
      10 => 'Vale Alimentação',
      11 => 'Vale Refeição',
      12 => 'Vale Presente',
      13 => 'Vale Combustível',
      15 => 'Boleto Bancário',
      16 => 'Depósito Bancário',
      17 => 'Pagamento Instantâneo (PIX)',
      18 => 'Transferência bancária, Carteira Digital',
      19 => 'Programa de fidelidade, Cashback, Crédito Virtual',
      90 => 'Sem pagamento',
      99 => 'Outros',
    ];

    return $lista[$type];
  }

  protected function calculateHeightPag()
  {
    $n = $this->pag && $this->pag->length > 0 ? $this->pag->length : 1;
    $height = 4 + (2.4 * $n) + 3;
    return $height;
  }
}

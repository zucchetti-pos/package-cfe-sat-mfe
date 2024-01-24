<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco totais do CFe
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoIV
{
  protected function blocoIV($y)
  {

    $valor = $this->getTagValue($this->total, 'vCFe') ?: 0.0;
    $desconto = $this->getTagValue($this->ICMSTot, 'vDesc') ?: 0.0;
    $bruto = $valor + $desconto;

    $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];

    $y -= 4;

    if ($this->canceled) {
      $y -= 4;
    }

    $texto = "Total R$";
    $this->pdf->textBox(
      $this->margem,
      $y,
      $this->wPrint / 2,
      3,
      $texto,
      $aFont,
      'T',
      'L',
      false,
      '',
      false
    );

    $texto = number_format((float) $bruto, 2, ',', '.');

    $this->pdf->textBox(
      $this->margem + $this->wPrint / 2,
      $y,
      $this->wPrint / 2,
      3,
      $texto,
      $aFont,
      'T',
      'R',
      false,
      '',
      false
    );

    return $this->bloco4H + $y;
  }
}

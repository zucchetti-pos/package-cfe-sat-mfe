<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco Avisos "De olho na Nota"
 */
trait TraitBlocoVIII
{
  protected function blocoVIII($y)
  {
    $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];

    $texto = "Consulte o QR Code pelo aplicativo \"De olho na nota\",\ndisponível na AppStore (Apple) e PlayStore (Android)";
    $y += $this->pdf->textBox($this->margem, $y - 3, $this->wPrint, 3, $texto, $aFont, 'T', 'C', false, '', false);

    return $this->bloco8H + $y;
  }
}

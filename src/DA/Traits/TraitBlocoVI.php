<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco VI informações do CFe: chave de acesso, série, emissão etc
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoVI
{
  protected function blocoVI2($y, $chave = '', $data = '', $hr = '')
  {

    // $y -= 4;

    $nseriesat = $this->formatField($this->getTagValue($this->ide, 'nserieSAT'), '###.###.###');

    $emissao = (new \DateTime($data . ' ' . $hr))->format('d/m/Y - H:i:s');

    if ($this->canceled) {
      $y -= 4;
    }

    $texto = "SAT Nº {$nseriesat}";
    $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];
    $y += (float) $this->pdf->textBox(
      $this->margem,
      $y,
      $this->wPrint,
      $this->bloco6H,
      $texto,
      $aFont,
      'T',
      'C',
      false,
      '',
      false
    );

    $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => ''];

    $y += (float) $this->pdf->textBox(
      $this->margem,
      $y,
      $this->wPrint,
      $this->bloco6H,
      $emissao,
      $aFont,
      'T',
      'C',
      false,
      '',
      false
    );

    $texto = $this->formatField($chave, $this->formatoChave);

    $aFont = ['font' => $this->fontePadrao, 'size' => 7, 'style' => 'B'];

    $y += (float) $this->pdf->textBox(
      $this->margem,
      $y + 3,
      $this->wPrint,
      $this->bloco6H,
      $texto,
      $aFont,
      'T',
      'C',
      false,
      '',
      false
    );

    return $y;
  }
}

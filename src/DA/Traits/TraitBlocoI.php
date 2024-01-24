<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco cabecalho com a identificação e logo do emitente
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoI
{
  protected function blocoI()
  {

    $y = $this->margem;

    // Dados do emitente
    $emitRazao = $this->getTagValue($this->emit, "xNome");
    // $emitFant = $this->getTagValue($this->emit, "xFant");
    $emitCnpj = $this->getTagValue($this->emit, "CNPJ");
    $emitIE = $this->getTagValue($this->emit, "IE");
    $emitIM = $this->getTagValue($this->emit, "IM");
    $emitCnpj = $this->formatField($emitCnpj, "###.###.###/####-##");

    // Endereço
    $emitLgr = $this->getTagValue($this->enderEmit, "xLgr");
    $emitNro = $this->getTagValue($this->enderEmit, "nro");
    $emitCpl = $this->getTagValue($this->enderEmit, "xCpl") ?: 'NÃO INFORMADO';
    $emitBairro = $this->getTagValue($this->enderEmit, "xBairro");
    $emitMun = $this->getTagValue($this->enderEmit, "xMun");
    $emitUF = 'SP';

    $maxHimg = $this->bloco1H - 4;

    if (!empty($this->logomarca)) {
      $xImg = $this->margem;
      $yImg = $this->margem + 1;
      $logoInfo = getimagesize($this->logomarca);
      $logoWmm = ($logoInfo[0] / 72) * 25.4;
      $logoHmm = ($logoInfo[1] / 72) * 25.4;
      $nImgW = $this->wPrint / 4;
      $nImgH = round($logoHmm * ($nImgW / $logoWmm), 0);
      if ($nImgH > $maxHimg) {
        $nImgH = $maxHimg;
        $nImgW = round($logoWmm * ($nImgH / $logoHmm), 0);
      }
      $xRs2 = ($nImgW) + $this->margem + 2;
      // $xRs = ($nImgW) + $this->margem;
      $wRs = ($this->wPrint - $nImgW);
      $alignH = 'L';
      $this->pdf->image($this->logomarca, $xImg, $yImg, $nImgW, $nImgH, 'jpeg');
    } else {
      $xRs2 = $this->margem;
      $wRs = $this->wPrint;
      $alignH = 'C';
    }

    $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => ''];
    $texto = "{$emitRazao}";
    $y += (float) $this->pdf->textBox(
      $xRs2,
      $this->margem,
      $wRs - 2,
      $this->bloco1H - $this->margem - 1,
      $texto,
      $aFont,
      'T',
      $alignH,
      false,
      '',
      true
    );

    if ($this->pdf->fontSizePt < 8) {
      $aFont = ['font' => $this->fontePadrao, 'size' => $this->pdf->fontSizePt, 'style' => ''];
    }

    $texto = $emitLgr . ", " . $emitNro;
    $y += (float) $this->pdf->textBox($xRs2, $y, $wRs - 2, 3, $texto, $aFont, 'T', $alignH, false, '', true);

    $texto = $emitCpl;
    $y += (float) $this->pdf->textBox($xRs2, $y, $wRs - 2, 3, $texto, $aFont, 'T', $alignH, false, '', true);

    $texto = $emitBairro;
    $y += (float) $this->pdf->textBox($xRs2, $y, $wRs - 2, 3, $texto, $aFont, 'T', $alignH, false, '', true);

    $texto = $emitMun . " - " . $emitUF;
    $y += (float) $this->pdf->textBox($xRs2, $y, $wRs - 2, 3, $texto, $aFont, 'T', $alignH, false, '', true);

    $y += 2;

    if ($emitIM) {

      $texto = "CNPJ {$emitCnpj}\nIE {$emitIE}\nIM {$emitIM}";
      $y += (float) $this->pdf->textBox($this->margem, $y, $this->wPrint, 3, $texto, $aFont, 'C', 'C', false, '', true);
    }

    //
    else {
      $texto = "CNPJ {$emitCnpj} IE {$emitIE} IM {$emitIM}";
      $y += (float) $this->pdf->textBox($this->margem, $y + 2, $this->wPrint, 3, $texto, $aFont, 'C', 'C', false, '', true) + 1;
    }

    $this->pdf->dashedHLine($this->margem, $y + 2, $this->wPrint, 0.1, 30);

    return $y;
  }
}

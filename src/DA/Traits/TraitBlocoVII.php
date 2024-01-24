<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

use Com\Tecnick\Barcode\Barcode;

/**
 * Bloco VII Código de barras e QRCode do CFe
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoVII
{

  private function addBarcode($w, $y, $chave)
  {

    $barcode = new Barcode();
    $bobj = $barcode->getBarcodeObj(
      'C128C',
      $chave,
      0,
      0,
      'black',
      array(0, 0, 0, 0)
    )->setBackgroundColor('white');

    $barcode = $bobj->getPngData();

    $wQr = $this->wPrint - $this->margem - 2;
    $hQr = 10;
    $yQr = ($y + 2);
    $xQr = ($w / 2) - ($wQr / 2);

    $pic = 'data://text/plain;base64,' . base64_encode($barcode);

    $this->pdf->image($pic, $xQr, $yQr, $wQr, $hQr, 'PNG');
  }

  protected function blocoVII2($y, $chave = '', $timestamp = '')
  {

    $y += 3;

    $maxW = $this->wPrint;
    $w = ($maxW * 1) + 4 + 2;

    // Caso seja 58mm
    if ($this->paperwidth < 65 + 4) {
      $this->addBarcode($w, $y, substr($chave, 0, 22));
      $y += 10 + $this->margem;
      $this->addBarcode($w, $y, substr($chave, 22));
    }

    // caso 80mm
    else {
      $this->addBarcode($w, $y, $chave);
    }

    $emitCnpj = $this->getTagValue($this->emit, "CNPJ");

    $emitCnpj = $emitCnpj ?? '';

    $valor = $this->getTagValue($this->total, 'vCFe');

    $signQrcode = $this->getTagValue($this->ide, 'assinaturaQRCODE');

    $qrcode = "{$chave}|{$timestamp}|{$valor}|{$emitCnpj}|{$signQrcode}";

    $barcode = new Barcode();
    $bobj = $barcode->getBarcodeObj(
      'QRCODE,M',
      $qrcode,
      -4,
      -4,
      'black',
      array(-2, -2, -2, -2)
    )->setBackgroundColor('white');

    $qrcode = $bobj->getPngData();
    $yQr2 = ($y + 10 + 6);
    $wQr = 50;
    $hQr = 50;
    $xQr = ($w / 2) - ($wQr / 2);
    $pic = 'data://text/plain;base64,' . base64_encode($qrcode);

    $this->pdf->image($pic, $xQr, $yQr2, $wQr, $hQr, 'PNG');

    return $this->bloco7H + $y + 2;
  }
}

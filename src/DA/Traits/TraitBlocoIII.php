<?php

namespace NFePHP\CfeSatMfe\DA\Traits;

/**
 * Bloco itens do CFe
 * 
 * @property \NFePHP\DA\Legacy\Pdf $pdf
 */
trait TraitBlocoIII
{
  protected function blocoIII($y)
  {
    if ($this->flagResume) {
      return $y;
    }

    $fsize = 7;

    if ($this->paperwidth < 70) {
      $fsize = 5;
    }

    $aFont = ['font' => $this->fontePadrao, 'size' => $fsize, 'style' => 'B'];

    $this->pdf->setTextColor(0, 0, 0);

    // if ($page === 1) {

    if ($this->paperwidth < 65) {
      $this->pdf->setTextColor(20, 20, 20);
    } else {
      $this->pdf->setTextColor(60, 60, 60);
    }

    $texto = "# | COD | DESC | QTD | UN | VL UN R$ | VL TR R$ | VL ITEM R$";
    $y += (float) $this->pdf->textBox($this->margem, $y - 6, $this->wPrint, 3, $texto, $aFont, 'C', 'L', false, '', true);

    $this->pdf->setTextColor(0, 0, 0);
    $this->pdf->dashedHLine($this->margem, $y - 4, $this->wPrint, 0.1, 30);
    // }

    if (!$this->det || !$this->det->length) return;

    // $start = 0;
    // $max = count($this->itens);

    // $offset = intVal(count($this->itens) / $maxpage);

    // if ($page === 1) {
    //   $starts = 0;
    //   $limit = ($offset * $page) - 5;
    // } else {
    //   $starts = $offset + 1;
    //   $limit = ($offset * $page) - 1;
    // }

    // if ($page === 1) {
    //   // $max = intVal(round($max / $page));
    //   // $start = $max - 1;
    //   // $max = intVal($max * $page);
    //   // die('m ' . $starts . '- ' . $offset * $page);
    // }

    // for (; $starts < $limit; $starts++) {
    //   $item = $this->itens[$starts];

    foreach ($this->itens as $index => $item) {
      $it = (object) $item;

      $aFont = ['font' => $this->fontePadrao, 'size' => $fsize, 'style' => $index % 2 === 0 ? 'B' : ''];

      $i = $index + 1;

      $linha1 = "{$i} {$it->codigo} {$it->desc}";
      $linha2 = "{$it->qtd} {$it->un} X {$it->vunit} ({$it->vtrib}) {$it->valor}";

      $y2 = (float) $this->pdf->textBox(
        $this->margem,
        $y - 3,
        $this->wPrint,
        $it->height,
        $linha1,
        $aFont,
        'T',
        'L',
        false,
        '',
        false
      );

      if (strlen($linha1) < 30) {
        $y += (float) $this->pdf->textBox(
          $this->margem,
          $y - 3,
          $this->wPrint,
          $it->height,
          $linha2,
          $aFont,
          'T',
          'R',
          false,
          '',
          true
        );

        // $y += $it->height;
        $y += 1;
      }

      //
      else {

        $y += $y2;

        $y += (float) $this->pdf->textBox(
          $this->margem,
          $y - 3,
          $this->wPrint,
          $it->height,
          $linha2,
          $aFont,
          'T',
          'R',
          false,
          '',
          true
        );

        $y += 1;
      }

      $y = $this->addPage($y);
    }

    // $this->pdf->dashedHLine($this->margem, $y + $it->height - 2, $this->wPrint, 0.1, 30);
    // $this->pdf->dashedHLine($this->margem, $y - 3, $this->wPrint, 0.1, 30);

    return $y;
  }

  protected function calculateHeightItens($descriptionWidth, &$max = null)
  {
    if ($this->flagResume || $this->canceled) {
      return 0;
    }

    $fsize = 7;

    if ($this->paperwidth < 70) {
      $fsize = 5;
    }

    $hfont = (imagefontheight($fsize) / 72) * 15;

    $htot = 0;
    if (!$this->det || $this->det->length == 0) return;

    foreach ($this->det as $i => $item) {
      $prod = $item->getElementsByTagName("prod")->item(0);
      $imposto = $item->getElementsByTagName("imposto")->item(0);
      $cProd = $this->getTagValue($prod, "cProd");
      $uCom = $this->getTagValue($prod, "uCom");
      $xProd = substr($this->getTagValue($prod, "xProd"), 0, 60);
      $qCom = number_format((float) $this->getTagValue($prod, "qCom"), 0, ",", ".");
      $vUnCom = number_format((float) $this->getTagValue($prod, "vUnCom"), 2, ",", ".");
      $vProd = number_format((float) $this->getTagValue($prod, "vProd"), 2, ",", ".");
      $vItem12741 = number_format((float) ($this->getTagValue($imposto, "vItem12741") ?: 0.0), 2, ",", ".");

      $tempPDF = new \NFePHP\DA\Legacy\Pdf();
      $tempPDF->setFont($this->fontePadrao, '', $fsize);

      $n = $tempPDF->wordWrap($xProd, $descriptionWidth);
      $limit = 60;

      while ($n > 2) {
        $limit -= 1;
        $xProd2 = substr($this->getTagValue($prod, "xProd"), 0, $limit);
        $p = $xProd2;
        $n = $tempPDF->wordWrap($p, $descriptionWidth);
      }

      $h = ($hfont * $n) + 0.5;

      // apenas adiciona se estiver contando quando o total
      if ($max === null) {
        $this->itens[] = [
          "codigo" => $cProd,
          "desc" => $xProd,
          "qtd" => $qCom,
          "un" => $uCom,
          "vtrib" => $vItem12741,
          "vunit" => $vUnCom,
          "valor" => $vProd,
          "height" => $h
        ];
      }

      $htot += $h + 1;

      if ($max === $i) return $htot + 2;
    }

    return $htot + 2;
  }
}

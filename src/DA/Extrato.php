<?php

namespace NFePHP\CfeSatMfe\DA;

use NFePHP\DA\Legacy\Dom;
use NFePHP\DA\Legacy\Pdf;
use NFePHP\DA\NFe\Danfce;

/**
 * Classe para a impressão em PDF do Documento Auxiliar do CFe SAT
 *
 * @category  Library
 * @package   NFePHP\CfeSatMfe
 * @copyright Copyright (c) 2021-2021
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Gustavo Lidani <gustavo@lidani.dev>
 * @link      http://github.com/nfephp-org/sped-cfe-sat for the canonical source repository
 */
class Extrato extends Danfce
{
  use Traits\TraitBlocoI,
    Traits\TraitBlocoII,
    Traits\TraitBlocoIII,
    Traits\TraitBlocoIV,
    Traits\TraitBlocoV,
    Traits\TraitBlocoVI,
    Traits\TraitBlocoVII,
    Traits\TraitBlocoVIII;

  protected $dashedLines = true;
  protected $paperwidth = 79;
  protected $descPercent = 1;
  protected $xml; // string XML NFe
  protected $logomarca = ''; // path para logomarca em jpg
  protected $formatoChave = "#### #### #### #### #### #### #### #### #### #### ####";
  protected $imgQRCode;
  protected $urlQR = '';
  protected $cfeCanc = '';
  protected $pdf;
  protected $margem = 4;
  protected $hMaxLinha = 5;
  protected $hBoxLinha = 6;
  protected $hLinha = 3;
  protected $aFontTit = ['font' => 'arial', 'size' => 9, 'style' => 'B'];
  protected $aFontTex = ['font' => 'arial', 'size' => 8, 'style' => ''];
  protected $submessage = null;

  protected $canceled = false;

  /**
   * @var string
   */
  protected $fontePadrao = 'arial';

  public $maxH = 297; //cabecalho

  protected $bloco1H = 18.0; //cabecalho
  protected $bloco2H = 12.0; //informação fiscal

  protected $bloco3H = 0.0; //itens
  protected $bloco4H = 13.0; //totais
  protected $bloco5H = 0.0; //formas de pagamento

  protected $bloco6H = 10.0; //informação para consulta
  protected $bloco7H = 70.0; //informações do consumidor
  protected $bloco8H = 75.0; //informações do consumidor

  protected $cfe;
  protected $infCFe;
  protected $obsFisco;
  protected $entrega;
  protected $total;

  public function setAmbiente($tpAmb)
  {
    $this->tpAmb = $tpAmb;
  }

  /**
   * Construtor
   *
   * @param string $xml
   *
   * @throws Exception
   */
  public function __construct($xml)
  {
    $this->xml = $xml;
    if (empty($xml)) {
      throw new \Exception('Um xml de CFe-SAT deve ser passado ao construtor da classe.');
    }

    //carrega dados do xml
    $this->loadXml();
  }

  protected function monta($logo = '')
  {

    if (!empty($logo)) {
      $this->logomarca = $this->adjustImage($logo, true);
    }

    // 138.025
    $tamPapelVert = $this->calculatePaperLength();

    $this->orientacao = 'P';
    $this->papel = [$this->paperwidth, min($tamPapelVert, $this->maxH)];
    $this->logoAlign = 'L';
    $this->pdf = new Pdf($this->orientacao, 'mm', $this->papel);

    //margens do PDF, em milímetros. Obs.: a margem direita é sempre igual à
    //margem esquerda. A margem inferior *não* existe na FPDF, é definida aqui
    //apenas para controle se necessário ser maior do que a margem superior
    $margSup = $this->margem;
    $margEsq = $this->margem;
    $margInf = $this->margem;

    // posição inicial do conteúdo, a partir do canto superior esquerdo da página
    $maxW = $this->paperwidth;
    $maxH = $tamPapelVert;

    //largura imprimivel em mm: largura da folha menos as margens esq/direita
    $this->wPrint = $maxW - ($margEsq * 2);
    //comprimento (altura) imprimivel em mm: altura da folha menos as margens
    //superior e inferior
    $this->hPrint = $maxH - $margSup - $margInf;

    // $this->pdf->aliasNbPages();
    // fixa as margens
    $this->pdf->setMargins($this->margesq, $this->margsup);
    $this->pdf->setDrawColor(0, 0, 0);
    $this->pdf->setFillColor(255, 255, 255);
    // inicia o documento
    $this->pdf->open();
    // adiciona a primeira página
    $this->pdf->addPage($this->orientacao, $this->papel);
    $this->pdf->setLineWidth(0.1);
    $this->pdf->settextcolor(0, 0, 0);

    // $y = 0;

    $y = $this->blocoI(); //cabecalho
    $y = $this->blocoII($y); // informação cabeçalho fiscal e mensagem de homologação

    if (!$this->flagResume && !$this->canceled) {
      $y = $this->blocoIII($y); // informação dos itens
    }

    $y = $this->blocoIV($y); // informação sobre os totais
    $y = $this->addPage($y);
    $y = $this->blocoV($y); // informação sobre pagamento
    $y = $this->addPage($y);

    if ($this->canceled) {
      $chave = str_replace('CFe', '', $this->infCFe->getAttribute("chCanc"));
      $data = $this->formatField($dEmi = $this->getTagValue($this->infCFe, "dEmi"), '####-##-##');
      $hr = $this->formatField($hEmi = $this->getTagValue($this->infCFe, "hEmi"), '##:##:##');

      $timestamp = $dEmi . $hEmi;

      $y = $this->blocoVI2($y, $chave, $data, $hr); // informações sobre chave acesso, data e série do sat
      $y = $this->addPage($y);
      $y = $this->blocoVII2($y, $chave, $timestamp); // BarCode e QRCODE
      $y = $this->addPage($y);

      $this->pdf->dashedHLine($this->margem, $y - 2, $this->wPrint, 0.1, 30);

      $texto = "DADOS DO CUPOM FISCAL ELETRÔNICO CANCELAMENTO";
      $aFont = ['font' => $this->fontePadrao, 'size' => 8, 'style' => 'B'];
      $y += (float) $this->pdf->textBox($this->margem, $y, $this->wPrint, 3, $texto, $aFont, 'T', 'C', false, '', false);

      $y += 12;
    }

    $chave = str_replace('CFe', '', $this->infCFe->getAttribute("Id"));
    $data = $this->formatField($dEmi = $this->getTagValue($this->ide, "dEmi"), '####-##-##');
    $hr = $this->formatField($hEmi = $this->getTagValue($this->ide, "hEmi"), '##:##:##');

    $timestamp = $dEmi . $hEmi;

    $y = $this->addPage($y);

    $y = $this->blocoVI2($y, $chave, $data, $hr); // informações sobre chave acesso, data e série do sat

    $y = $this->addPage($y, $this->paperwidth < 60 ? 70 : 60);

    $y = $this->blocoVII2($y, $chave, $timestamp); // BarCode e QRCODE

    $y = $this->addPage($y);

    $y = $this->blocoVIII($y); // OBS De olho na nota
  }

  protected function addPage($y, $offset = 6)
  {
    if ($y < $this->maxH - $offset) return $y;

    $this->pdf->addPage($this->orientacao, $this->papel);
    $this->pdf->setLineWidth(0.1); // define a largura da linha
    $this->pdf->setTextColor(0, 0, 0);

    return $this->margem;
  }

  /**
   * Carrega os dados do xml na classe
   * @param string $xml
   *
   * @throws InvalidArgumentException
   */
  private function loadXml()
  {
    $this->dom = new Dom();
    $this->dom->loadXML($this->xml);
    $this->ide = $this->dom->getElementsByTagName("ide")->item(0);
    $mod = $this->getTagValue($this->ide, "mod");

    if ($mod != '59') {
      throw new \Exception("O xml do Extrato deve ser um CFe-SAT modelo 59");
    }

    $this->tpAmb = $this->getTagValue($this->ide, 'tpAmb');

    $this->cfe = $this->dom->getElementsByTagName("CFe")->item(0);

    $this->infCFe = $this->dom->getElementsByTagName("infCFe")->item(0);
    $this->emit = $this->dom->getElementsByTagName("emit")->item(0);
    $this->enderEmit = $this->dom->getElementsByTagName("enderEmit")->item(0);
    $this->dest = $this->dom->getElementsByTagName("dest")->item(0);
    $this->total = $this->dom->getElementsByTagName("total")->item(0);

    if ($this->cfe) {

      $this->det = $this->dom->getElementsByTagName("det");
      $this->entrega = $this->dom->getElementsByTagName("entrega")->item(0);
      $this->imposto = $this->dom->getElementsByTagName("imposto")->item(0);
      $this->ICMSTot = $this->dom->getElementsByTagName("ICMSTot")->item(0);

      $this->infAdic = $this->dom->getElementsByTagName("infAdic")->item(0);
      $this->obsFisco = $this->dom->getElementsByTagName("obsFisco");

      $this->pag = $this->dom->getElementsByTagName("MP");
      $tagPag = $this->dom->getElementsByTagName("pgto")->item(0);

      $this->vTroco = $this->getTagValue($tagPag, "vTroco");
    }

    //
    else {

      $this->cfeCanc = $this->dom->getElementsByTagName("CFeCanc")->item(0);
      $this->setAsCanceled();
    }
  }

  private function calculatePaperLength()
  {
    $wprint = $this->paperwidth - (2 * $this->margem);

    $this->bloco3H = $this->calculateHeightItens($wprint * $this->descPercent);
    $this->bloco5H = $this->calculateHeightPag();

    if ($this->canceled) {
      $this->bloco8H += 35;
    }

    //
    else {
      // sempre joga mais papel pra garantir, na impressão vai ignorar
      $this->bloco8H += 10;
    }

    return $this->bloco1H //cabecalho
      + $this->bloco2H //informação fiscal
      + $this->bloco3H //itens
      + $this->bloco4H //totais
      + $this->bloco5H //formas de pagamento
      + $this->bloco6H //informação para consulta
      + $this->bloco7H //informações do consumidor
      + $this->bloco8H; //qrcode
  }
}

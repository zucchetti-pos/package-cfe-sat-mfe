<?php

namespace NFePHP\CfeSatMfe;

/**
 * Classe a construção do xml do CFe modelo 59
 * Esta classe basica está estruturada para montar XML do CFe para o layout versão 0.07
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

use NFePHP\Common\DOMImproved as Dom;
use NFePHP\Common\Strings;
use NFePHP\CfeSatMfe\Common\Gtin;
use stdClass;
use RuntimeException;
use DOMElement;

class Make
{

  /**
   * @var array
   */
  public $errors = [];
  /**
   * @var string
   */
  public $xml;
  /**
   * @var string
   */
  protected $version = '0.07';
  /**
   * @var integer
   */
  protected $mod = 59;
  /**
   * @var \NFePHP\Common\DOMImproved
   */
  public $dom;
  /**
   * @var integer
   */
  protected $tpAmb = 2;
  /**
   * @var stdClass
   */
  public $stdTot;
  /**
   * @var DOMElement
   */
  protected $CFe;
  /**
   * @var DOMElement
   */
  protected $infCFe;
  /**
   * @var DOMElement
   */
  protected $ide;
  /**
   * @var DOMElement
   */
  protected $emit;
  /**
   * @var DOMElement
   */
  protected $dest;
  /**
   * @var DOMElement
   */
  protected $entrega;
  /**
   * @var DOMElement
   */
  protected $total;
  /**
   * @var DOMElement
   */
  protected $infAdic;
  /**
   * @var DOMElement
   */
  protected $pgto;
  /**
   * @var array of DOMElements
   */
  protected $aProd = [];
  /**
   * @var array of DOMElements
   */
  protected $aImposto = [];
  /**
   * @var array of DOMElements
   */
  protected $aICMS = [];
  /**
   * @var array of DOMElements
   */
  protected $aISSQN = [];
  /**
   * @var array
   */
  protected $aPIS = [];
  /**
   * @var array of DOMElements
   */
  protected $aPISST = [];
  /**
   * @var array of DOMElements
   */
  protected $aCOFINS = [];
  /**
   * @var array of DOMElements
   */
  protected $aCOFINSST = [];
  /**
   * @var array of DOMElements
   */
  protected $aInfAdProd = [];
  /**
   * @var array of DOMElements
   */
  protected $aobsFiscoDet = [];
  /**
   * @var array of DOMElements
   */
  protected $aDet = [];

  /**
   * @var boolean
   */
  protected $replaceAccentedChars = false;

  /**
   * Função construtora cria um objeto DOMDocument
   * que será carregado com o documento fiscal
   */
  public function __construct()
  {
    $this->dom = new Dom('1.0', 'UTF-8');
    $this->dom->preserveWhiteSpace = false;
    $this->dom->formatOutput = false;

    $this->stdTot = new \stdClass;
    $this->stdTot->vCFeLei12741 = 0.0;
    $this->stdTot->vDescSubtot = 0.0;
    $this->stdTot->vAcresSubtot = 0.0;
  }

  /**
   * Set character convertion to ASCII only ou not
   * @param bool $option
   */
  public function setOnlyAscii($option = false)
  {
    $this->replaceAccentedChars = $option;
  }

  /**
   * Returns xml string and assembly it is necessary
   * @return string
   */
  public function getXML()
  {
    if (empty($this->xml)) {
      $this->montaCFe();
    }

    return $this->xml;
  }

  /**
   * Returns the model of CFe = 59
   * @return int
   */
  public function getModelo()
  {
    return $this->mod;
  }

  /**
   * Call method of xml assembly. For compatibility only.
   * @return string
   */
  public function montaCFe()
  {
    return $this->monta();
  }

  /**
   * CFe xml mount method
   * this function returns TRUE on success or FALSE on error
     * The xml of the CFe must be retrieved by the getXML() function or
   * directly by the public property $xml
   *
   * @return string
   * @throws RuntimeException
   */
  public function monta()
  {
    if (!empty($this->errors)) {
      $this->errors = array_merge($this->errors, $this->dom->errors);
    } else {
      $this->errors = $this->dom->errors;
    }

    //cria a tag raiz do CFe
    $this->buildCFe();

    // Cria as tags dos produtos
    $this->buildDet();

    //[2] tag ide (5 B01)
    $this->dom->appChild($this->infCFe, $this->ide, 'Falta tag "infCFe"');
    //[8] tag emit (30 C01)
    $this->dom->appChild($this->infCFe, $this->emit, 'Falta tag "infCFe"');

    // se não houver a tag dest
    if (!$this->dest)
      // cria a tag vazia
      $this->tagdest(new \stdClass);

    //[10] tag dest (62 E01)
    $this->dom->appChild($this->infCFe, $this->dest, 'Falta tag "infCFe"');
    //[13] tag entrega (89 G01)

    // se houver cliente
    if ($this->dest)
      // Adiciona o endereço (se houver)
      $this->dom->appChild($this->infCFe, $this->entrega, 'Falta tag "infCFe"');

    //[14a] tag det (98 H01)
    foreach ($this->aDet as $det) {

      // Se passar de 500 itens, não insere
      if ($this->infCFe->getElementsByTagName('det')->count() >= 500)
        throw new RuntimeException('O CF-e/SAT aceita um XML com no máximo 500 itens.');

      $this->dom->appChild($this->infCFe, $det, 'Falta tag "infCFe"');
    }

    // se nao tiver total
    if (!$this->total)
      // Cria o total
      $this->total = $this->tagTotal(new \stdClass);

    //[28a] tag total (326 W01)
    $this->dom->appChild($this->infCFe, $this->total, 'Falta tag "infCFe"');

    $this->dom->appChild($this->infCFe, $this->pgto, 'Falta tag "infCFe"');

    $this->dom->appChild($this->infCFe, $this->infAdic, 'Falta tag "infCFe"');

    //[1] tag infCFe (1 A01)
    $this->dom->appChild($this->CFe, $this->infCFe, 'Falta tag "CFe"');

    //[0] tag CFe
    $this->dom->appendChild($this->CFe);

    // Salva o xml
    $this->xml = $this->dom->saveXML();

    if (count($this->errors) > 0) {
      throw new RuntimeException('Existem erros nas tags. Obtenha os erros com getErrors().');
    }

    // Retorna o xml
    return $this->xml;
  }

  /**
   * Informações da NF-e A01 pai CFe
   * tag CFe/infCFe
   * @param  stdClass $std
   * @return DOMElement
   */
  public function taginfCFe(stdClass $std)
  {
    $possible = [
      'versaoDadosEnt',
    ];

    $std = $this->equilizeParameters($std, $possible);

    $this->infCFe = $this->dom->createElement("infCFe");

    $this->infCFe->setAttribute(
      "versaoDadosEnt",
      $std->versaoDadosEnt
    );

    $this->version = $std->versaoDadosEnt;

    return $this->infCFe;
  }

  /**
   * Informações de identificação da NF-e B01 pai A01
   * tag CFe/infCFe/ide
   * @param  stdClass $std
   * @return DOMElement
   */
  public function tagide(stdClass $std)
  {
    $possible = [
      'CNPJ',
      'signAC',
      'numeroCaixa'
    ];

    $std = $this->equilizeParameters($std, $possible);

    $identificador = 'B01 <ide> - ';
    $ide = $this->dom->createElement("ide");

    $this->dom->addChild(
      $ide,
      "CNPJ",
      $std->CNPJ,
      false,
      $identificador . "CNPJ da Software House"
    );
    $this->dom->addChild(
      $ide,
      "signAC",
      $std->signAC,
      false,
      $identificador . "Assinatura do Aplicativo Comercial"
    );
    $this->dom->addChild(
      $ide,
      "numeroCaixa",
      $std->numeroCaixa,
      true,
      $identificador . "Número do Caixa onde o CF-e foi emitido"
    );

    $this->ide = $ide;
    return $ide;
  }

  /**
   * Identificação do emitente do CF-e C01 pai A01
   * tag CFe/infCFe/emit
   * @param  stdClass $std
   * @return DOMElement
   */
  public function tagemit(stdClass $std)
  {
    $possible = [
      'CNPJ',
      'IE',
      'cRegTribISSQN',
      'indRatISSQN',
      'IM',
    ];

    $std = $this->equilizeParameters($std, $possible);
    $identificador = 'C01 <emit> - ';
    $this->emit = $this->dom->createElement("emit");

    $this->dom->addChild(
      $this->emit,
      "CNPJ",
      Strings::onlyNumbers($std->CNPJ),
      true,
      $identificador . "CNPJ do emitente"
    );
    $this->dom->addChild(
      $this->emit,
      "IE",
      $std->IE,
      true,
      $identificador . "Inscrição Estadual do emitente"
    );
    $this->dom->addChild(
      $this->emit,
      "IM",
      $std->IM,
      false,
      $identificador . "Inscrição Municipal do emitente"
    );
    $this->dom->addChild(
      $this->emit,
      "cRegTribISSQN",
      $std->cRegTribISSQN,
      false,
      $identificador . "cRegTribISSQN"
    );
    $this->dom->addChild(
      $this->emit,
      "indRatISSQN",
      $std->indRatISSQN,
      false,
      $identificador . "indRatISSQN"
    );

    return $this->emit;
  }

  /**
   * Identificação do Destinatário do CF-e E01 pai A01
   * tag CFe/infCFe/dest
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagdest(stdClass $std)
  {
    $possible = [
      'CNPJ',
      'CPF',
      'xNome'
    ];

    $std = $this->equilizeParameters($std, $possible);
    $identificador = 'E01 <dest> - ';
    $this->dest = $this->dom->createElement("dest");

    // Se for CNPJ
    if (!empty($std->CNPJ)) {
      $this->dom->addChild(
        $this->dest,
        "CNPJ",
        Strings::onlyNumbers($std->CNPJ),
        false,
        $identificador . "CNPJ do destinatário"
      );
    }

    // Se for CPF
    else if (!empty($std->CPF)) {
      $this->dom->addChild(
        $this->dest,
        "CPF",
        Strings::onlyNumbers($std->CPF),
        false,
        $identificador . "CPF do destinatário"
      );
    }

    $this->dom->addChild(
      $this->dest,
      "xNome",
      substr(trim($std->xNome), 0, 60),
      false,
      $identificador . "Razão Social ou nome do destinatário"
    );

    return $this->dest;
  }

  /**
   * G - Identificação do Local de Entrega
   * tag CFe/infCFe/entrega
   * Informar apenas no caso de entrega da mercadoria em domicílio
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagentrega(stdClass $std)
  {

    $possible = [
      'xLgr',
      'nro',
      'xCpl',
      'xBairro',
      'xMun',
      'UF',
    ];

    $std = $this->equilizeParameters($std, $possible);

    $identificador = 'G01 <entrega> - ';

    $this->entrega = $this->dom->createElement("entrega");

    $this->dom->addChild(
      $this->entrega,
      "xLgr",
      $std->xLgr,
      true,
      $identificador . "Logradouro do Endereço do Destinatário"
    );
    $this->dom->addChild(
      $this->entrega,
      "nro",
      $std->nro,
      true,
      $identificador . "Número do Endereço do Destinatário"
    );
    $this->dom->addChild(
      $this->entrega,
      "xCpl",
      $std->xCpl,
      false,
      $identificador . "Complemento do Endereço do Destinatário"
    );
    $this->dom->addChild(
      $this->entrega,
      "xBairro",
      $std->xBairro,
      true,
      $identificador . "Bairro do Endereço do Destinatário"
    );
    $this->dom->addChild(
      $this->entrega,
      "xMun",
      $std->xMun,
      true,
      $identificador . "Nome do município do Endereço do Destinatário"
    );
    $this->dom->addChild(
      $this->entrega,
      "UF",
      $std->UF,
      true,
      $identificador . "Sigla da UF do Endereço do Destinatário"
    );

    return $this->entrega;
  }

  /**
   * I - Detalhamento de Produtos e Serviços do CF-e
   * tag CFe/infCFe/det[]/prod
   * Múltiplas ocorrências (máximo = 500)
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagprod(stdClass $std)
  {
    $possible = [
      'item',
      'cProd',
      'cEAN',
      'xProd',
      'NCM',
      'CEST',
      'CFOP',
      'uCom',
      'qCom',
      'vUnCom',
      'indRegra',
      'vOutro',
      'vDesc'
    ];

    $std = $this->equilizeParameters($std, $possible);

    $cean = !empty($std->cEAN) ? trim(strtoupper($std->cEAN)) : '';
    $ceantrib = !empty($std->cEANTrib) ? trim(strtoupper($std->cEANTrib)) : '';
    // throw exception if not is Valid
    try {
      Gtin::isValid($cean);
    } catch (\InvalidArgumentException $e) {
      $this->errors[] = "cEANT {$cean} " . $e->getMessage();
    }

    try {
      Gtin::isValid($ceantrib);
    } catch (\InvalidArgumentException $e) {
      $this->errors[] = "cEANTrib {$ceantrib} " . $e->getMessage();
    }

    $identificador = 'I01 <prod> - ';
    $prod = $this->dom->createElement("prod");
    $this->dom->addChild(
      $prod,
      "cProd",
      $std->cProd,
      true,
      $identificador . "[item $std->item] Código do produto ou serviço"
    );
    $this->dom->addChild(
      $prod,
      "cEAN",
      $cean,
      false,
      $identificador . "[item $std->item] GTIN (Global Trade Item Number) do produto, antigo código EAN ou código de barras"
    );
    $this->dom->addChild(
      $prod,
      "xProd",
      $std->xProd,
      true,
      $identificador . "[item $std->item] Descrição do produto ou serviço"
    );
    $this->dom->addChild(
      $prod,
      "NCM",
      $std->NCM,
      false,
      $identificador . "[item $std->item] Código NCM com 8 dígitos ou 2 dígitos (gênero)"
    );
    // se for a partir do layout 0.08
    if ($this->version >= '0.08')
      $this->dom->addChild(
        $prod,
        "CEST",
        $std->CEST,
        false,
        $identificador . "[item $std->item] Código CEST que identifica a mercadoria sujeita aos regimes de substituição tributária e de antecipação do recolhimento do imposto."
      );
    $this->dom->addChild(
      $prod,
      "CFOP",
      $std->CFOP,
      true,
      $identificador . "[item $std->item] Código Fiscal de Operações e Prestações"
    );
    $this->dom->addChild(
      $prod,
      "uCom",
      $std->uCom,
      true,
      $identificador . "[item $std->item] Unidade Comercial do produto"
    );
    $this->dom->addChild(
      $prod,
      "qCom",
      $this->conditionalNumberFormatting($std->qCom, 4),
      true,
      $identificador . "[item $std->item] Quantidade Comercial do produto"
    );
    $this->dom->addChild(
      $prod,
      "vUnCom",
      $this->conditionalNumberFormatting($std->vUnCom, 2), // 10
      true,
      $identificador . "[item $std->item] Valor Unitário de Comercialização do produto"
    );
    $this->dom->addChild(
      $prod,
      "indRegra",
      $std->indRegra,
      true,
      $identificador . "[item $std->item] Indicador da regra de cálculo utilizada para Valor Bruto dos Produtos e Serviços: A - Arredondamento; T - Truncamento"
    );
    $this->dom->addChild(
      $prod,
      "vDesc",
      $this->conditionalNumberFormatting($std->vDesc),
      false,
      $identificador . "[item $std->item] Valor do Desconto"
    );
    $this->dom->addChild(
      $prod,
      "vOutro",
      $this->conditionalNumberFormatting($std->vOutro),
      false,
      $identificador . "[item $std->item] Outras despesas acessórias"
    );

    $this->aProd[$std->item] = $prod;
    return $prod;
  }

  /**
   * I17 Grupo do campo de uso livre do Fisco
   * tag CFe/infCFe/det[]/obsFiscoDet
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagobsFiscoDet(stdClass $std)
  {
    $possible = ['item', 'xCampoDet', 'xTextoDet'];

    $std = $this->equilizeParameters($std, $possible);
    $obsFiscoDet = $this->dom->createElement("obsFiscoDet");

    $identificador = 'I17 - <obsFiscoDet>';

    $obsFiscoDet->setAttribute(
      "xCampoDet",
      $std->xCampoDet
    );

    $this->dom->addChild(
      $obsFiscoDet,
      "xTextoDet",
      $std->xTextoDet,
      true,
      $identificador . "[item $std->item] Conteúdo do campo."
    );

    $this->aobsFiscoDet[$std->item][] = $obsFiscoDet;
    return $obsFiscoDet;
  }

  /**
   * Grupo de Tributos incidentes no Produto ou Serviço
   * tag CFe/infCFe/det[]/imposto
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagimposto(stdClass $std)
  {
    $possible = [
      'item', 'vItem12741'
    ];

    $std = $this->equilizeParameters($std, $possible);

    $identificador = 'M01 <imposto> - ';
    $imposto = $this->dom->createElement("imposto");

    $this->dom->addChild(
      $imposto,
      "vItem12741",
      $this->conditionalNumberFormatting($std->vItem12741, 2),
      false,
      sprintf('%s [item %s] Valor aproximado dos tributos do produto ou serviço, declarado pelo emitente, conforme Lei 12741/2012.', $identificador, $std->item),
    );

    $this->stdTot->vCFeLei12741 += !empty($std->vItem12741) ? floatVal($std->vItem12741) : 0.0;

    $this->aImposto[$std->item] = $imposto;
    return $imposto;
  }

  /**
   * Grupo do ICMS da Operação própria e ST; ocorr: 0-1
   * tag CFe/infCFe/det[]/imposto/ICMS
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagICMS(stdClass $std)
  {
    $possible = [
      'item',
      'Orig',
      'CST',
      'CSOSN',
      'pICMS'
    ];
    $std = $this->equilizeParameters($std, $possible);

    $identificador = 'N01 <ICMSxx> - ';
    switch ($std->CST) {
      case '00':
      case '20':
      case '90':
        $icms = $this->dom->createElement("ICMS00");
        $this->dom->addChild(
          $icms,
          'Orig',
          $std->Orig,
          true,
          sprintf('%s [item %s] Origem da mercadoria', $identificador, $std->item),
        );
        $this->dom->addChild(
          $icms,
          'CST',
          $std->CST,
          true,
          sprintf('%s [item %s] Tributação do ICMS = 00', $identificador, $std->item),
        );

        $this->dom->addChild(
          $icms,
          'pICMS',
          $this->conditionalNumberFormatting($std->pICMS, 2),
          true,
          sprintf('%s [item %s] Alíquota do imposto', $identificador, $std->item),
        );
        break;
      case '40':
      case '41':
      case '60':
        $icms = $this->dom->createElement("ICMS40");
        $this->dom->addChild(
          $icms,
          'Orig',
          $std->Orig,
          true,
          sprintf('%s [item %s] Origem da mercadoria', $identificador, $std->item),
        );
        $this->dom->addChild(
          $icms,
          'CST',
          $std->CST,
          true,
          sprintf('%s [item %s] Tributação do ICMS', $identificador, $std->item),
        );
        break;
    }

    switch ($std->CSOSN) {
      case '102':
      case '300':
      case '400':
      case '500':
        $icms = $this->dom->createElement("ICMSSN102");
        $this->dom->addChild(
          $icms,
          'Orig',
          $std->Orig,
          true,
          sprintf('%s [item %s] Origem da mercadoria', $identificador, $std->item),
        );
        $this->dom->addChild(
          $icms,
          'CSOSN',
          $std->CSOSN,
          true,
          sprintf('%s [item %s] Código de Situação da Operação – Simples Nacional', $identificador, $std->item),
        );
        break;
      case '900':
        $icms = $this->dom->createElement("ICMSSN900");
        $this->dom->addChild(
          $icms,
          'Orig',
          $std->Orig,
          true,
          sprintf('%s [item %s] Origem da mercadoria', $identificador, $std->item),
        );
        $this->dom->addChild(
          $icms,
          'CSOSN',
          $std->CSOSN,
          true,
          sprintf('%s [item %s] Código de Situação da Operação - Simples Nacional', $identificador, $std->item),
        );
        $this->dom->addChild(
          $icms,
          'pICMS',
          $this->conditionalNumberFormatting($std->pICMS, 2),
          true,
          sprintf('%s [item %s] Alíquota do imposto', $identificador, $std->item),
        );
        break;
    }
    $tagIcms = $this->dom->createElement('ICMS');

    // Se foi criado a tag icms
    // Se acaso não foi criada, como a tag é opcional, nem adiciona no xml
    if (isset($icms)) {

      // se ainda não foi criado a tag imposto
      if (empty($this->aImposto[$std->item])) {

        $imposto = new \stdClass;
        $imposto->item = $std->item;
        // Cria a tag imposto automaticamente
        $this->tagimposto($imposto);
      }

      $tagIcms->appendChild($icms);

      $this->aICMS[$std->item] = $tagIcms;
    }

    return $tagIcms;
  }

  /**
   * Q01 Grupo do PIS
   * tag CFe/infCFe/det[]/imposto/PIS
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagPIS(stdClass $std)
  {
    $possible = [
      'item',
      'CST',
      'vBC',
      'pPIS',
      'qBCProd',
      'vAliqProd'
    ];
    $std = $this->equilizeParameters($std, $possible);

    switch ($std->CST) {
      case '01':
      case '02':
      case '05':
        $pisItem = $this->dom->createElement('PISAliq');
        $this->dom->addChild(
          $pisItem,
          'CST',
          $std->CST,
          true,
          "[item $std->item] Código de Situação Tributária do PIS"
        );
        $this->dom->addChild(
          $pisItem,
          'vBC',
          $this->conditionalNumberFormatting($std->vBC),
          true,
          "[item $std->item] Valor da Base de Cálculo do PIS"
        );
        $this->dom->addChild(
          $pisItem,
          'pPIS',
          substr($this->conditionalNumberFormatting($std->pPIS, 4), 0, 6),
          true,
          "[item $std->item] Alíquota do PIS (em percentual)"
        );
        break;
      case '03':
        $pisItem = $this->dom->createElement('PISQtde');
        $this->dom->addChild(
          $pisItem,
          'CST',
          $std->CST,
          true,
          "[item $std->item] Código de Situação Tributária do PIS"
        );
        $this->dom->addChild(
          $pisItem,
          'qBCProd',
          $this->conditionalNumberFormatting($std->qBCProd, 4),
          true,
          "[item $std->item] Quantidade Vendida"
        );
        $this->dom->addChild(
          $pisItem,
          'vAliqProd',
          $this->conditionalNumberFormatting($std->vAliqProd, 4),
          true,
          "[item $std->item] Alíquota do PIS (em reais)"
        );
        break;
      case '04':
      case '06':
      case '07':
      case '08':
      case '09':
        $pisItem = $this->dom->createElement('PISNT');
        $this->dom->addChild(
          $pisItem,
          'CST',
          $std->CST,
          true,
          "[item $std->item] Código de Situação Tributária do PIS"
        );
        break;
      case '49':
        $pisItem = $this->dom->createElement('PISSN');
        $this->dom->addChild(
          $pisItem,
          'CST',
          $std->CST,
          true,
          "[item $std->item] Código de Situação Tributária do PIS"
        );
        break;
        // case '50':
        // case '51':
        // case '52':
        // case '53':
        // case '54':
        // case '55':
        // case '56':
        // case '60':
        // case '61':
        // case '62':
        // case '63':
        // case '64':
        // case '65':
        // case '66':
        // case '67':
        // case '70':
        // case '71':
        // case '72':
        // case '73':
        // case '74':
        // case '75':
        // case '98':
      case '99':
        $pisItem = $this->dom->createElement('PISOutr');
        $this->dom->addChild(
          $pisItem,
          'CST',
          $std->CST,
          true,
          "[item $std->item] Código de Situação Tributária do PIS"
        );
        if (!isset($std->qBCProd)) {
          $this->dom->addChild(
            $pisItem,
            'vBC',
            $this->conditionalNumberFormatting($std->vBC),
            ($std->vBC !== null) ? true : false,
            "[item $std->item] Valor da Base de Cálculo do PIS"
          );
          $this->dom->addChild(
            $pisItem,
            'pPIS',
            substr($this->conditionalNumberFormatting($std->pPIS, 4), 0, 6),
            ($std->pPIS !== null) ? true : false,
            "[item $std->item] Alíquota do PIS (em percentual)"
          );
        } else {
          $this->dom->addChild(
            $pisItem,
            'qBCProd',
            $this->conditionalNumberFormatting($std->qBCProd, 4),
            ($std->qBCProd !== null) ? true : false,
            "[item $std->item] Quantidade Vendida"
          );
          $this->dom->addChild(
            $pisItem,
            'vAliqProd',
            $this->conditionalNumberFormatting($std->vAliqProd, 4),
            ($std->vAliqProd !== null) ? true : false,
            "[item $std->item] Alíquota do PIS (em reais)"
          );
        }
        break;
    }

    $pis = $this->dom->createElement('PIS');
    if (isset($pisItem)) {

      // se ainda não foi criado a tag imposto
      if (empty($this->aImposto[$std->item])) {

        $imposto = new \stdClass;
        $imposto->item = $std->item;
        // Cria a tag imposto automaticamente
        $this->tagimposto($imposto);
      }

      $pis->appendChild($pisItem);
    }

    $this->aPIS[$std->item] = $pis;
    return $pis;
  }

  /**
   * Grupo de PIS Substituição Tributária
   * tag CFe/infCFe/det[]/imposto/PISST (opcional)
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagPISST(stdClass $std)
  {
    $possible = [
      'item',
      'vBC',
      'pPIS',
      'qBCProd',
      'vAliqProd'
    ];

    $std = $this->equilizeParameters($std, $possible);
    $pisst = $this->dom->createElement('PISST');

    if (!isset($std->qBCProd)) {
      $this->dom->addChild(
        $pisst,
        'vBC',
        $this->conditionalNumberFormatting($std->vBC),
        false,
        "[item $std->item] Valor da Base de Cálculo do PIS"
      );
      $this->dom->addChild(
        $pisst,
        'pPIS',
        substr($this->conditionalNumberFormatting($std->pPIS, 4), 0, 6),
        false,
        "[item $std->item] Alíquota do PIS (em percentual)"
      );
    } else {
      $this->dom->addChild(
        $pisst,
        'qBCProd',
        $this->conditionalNumberFormatting($std->qBCProd, 4),
        false,
        "[item $std->item] Quantidade Vendida"
      );
      $this->dom->addChild(
        $pisst,
        'vAliqProd',
        $this->conditionalNumberFormatting($std->vAliqProd, 4),
        false,
        "[item $std->item] Alíquota do PIS (em reais)"
      );
    }

    $this->aPISST[$std->item] = $pisst;
    return $pisst;
  }

  /**
   * Grupo COFINS S01 pai M01
   * tag CFe/infCFe/det[]/imposto/COFINS (opcional)
   * @param  stdClass $std
   * @return DOMElement
   */
  public function tagCOFINS(stdClass $std)
  {
    $possible = [
      'item',
      'CST',
      'vBC',
      'pCOFINS',
      'qBCProd',
      'vAliqProd'
    ];
    $std = $this->equilizeParameters($std, $possible);
    switch ($std->CST) {
      case '01':
      case '02':
      case '05':
        $cofinsItem = $this->dom->createElement('COFINSAliq');
        $this->dom->addChild(
          $cofinsItem,
          'CST',
          $std->CST,
          true,
          "Código de Situação Tributária da COFINS"
        );
        $this->dom->addChild(
          $cofinsItem,
          'vBC',
          $this->conditionalNumberFormatting($std->vBC),
          true,
          "Valor da Base de Cálculo da COFINS"
        );
        $this->dom->addChild(
          $cofinsItem,
          'pCOFINS',
          substr($this->conditionalNumberFormatting($std->pCOFINS, 4), 0, 6),
          true,
          "Alíquota da COFINS (em percentual)"
        );
        break;
      case '03':
        $cofinsItem = $this->dom->createElement('COFINSQtde');
        $this->dom->addChild(
          $cofinsItem,
          'CST',
          $std->CST,
          true,
          "[item $std->item] Código de Situação Tributária da COFINS"
        );
        $this->dom->addChild(
          $cofinsItem,
          'qBCProd',
          $this->conditionalNumberFormatting($std->qBCProd, 4),
          true,
          "[item $std->item] Quantidade Vendida"
        );
        $this->dom->addChild(
          $cofinsItem,
          'vAliqProd',
          $this->conditionalNumberFormatting($std->vAliqProd, 4),
          true,
          "[item $std->item] Alíquota do COFINS (em reais)"
        );
        break;
      case '04':
      case '06':
      case '07':
      case '08':
      case '09':
        $cofinsItem = $this->dom->createElement('COFINSNT');
        $this->dom->addChild(
          $cofinsItem,
          "CST",
          $std->CST,
          true,
          "Código de Situação Tributária da COFINS"
        );
        break;
      case '49':
        $cofinsItem = $this->dom->createElement('COFINSSN');
        $this->dom->addChild(
          $cofinsItem,
          "CST",
          $std->CST,
          true,
          "Código de Situação Tributária da COFINS"
        );
        break;
      case '99':
        $cofinsItem = $this->dom->createElement('COFINSOutr');
        $this->dom->addChild(
          $cofinsItem,
          "CST",
          $std->CST,
          true,
          "Código de Situação Tributária da COFINS"
        );
        if (!isset($std->qBCProd)) {
          $this->dom->addChild(
            $cofinsItem,
            "vBC",
            $this->conditionalNumberFormatting($std->vBC),
            ($std->vBC !== null) ? true : false,
            "Valor da Base de Cálculo da COFINS"
          );
          $this->dom->addChild(
            $cofinsItem,
            "pCOFINS",
            substr($this->conditionalNumberFormatting($std->pCOFINS, 4), 0, 6),
            ($std->pCOFINS !== null) ? true : false,
            "Alíquota da COFINS (em percentual)"
          );
        } else {
          $this->dom->addChild(
            $cofinsItem,
            "qBCProd",
            $this->conditionalNumberFormatting($std->qBCProd, 4),
            ($std->qBCProd !== null) ? true : false,
            "Quantidade Vendida"
          );
          $this->dom->addChild(
            $cofinsItem,
            "vAliqProd",
            $this->conditionalNumberFormatting($std->vAliqProd, 4),
            ($std->vAliqProd !== null) ? true : false,
            "Alíquota da COFINS (em reais)"
          );
        }
        break;
    }
    $cofins = $this->dom->createElement('COFINS');
    if (isset($cofinsItem)) {
      $cofins->appendChild($cofinsItem);
    }
    $this->aCOFINS[$std->item] = $cofins;
    return $cofins;
  }

  /**
   * Grupo COFINS Substituição Tributária T01 pai M01
   * tag CFe/infCFe/det[]/imposto/COFINSST (opcional)
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagCOFINSST(stdClass $std)
  {
    $possible = [
      'item',
      'vBC',
      'pCOFINS',
      'qBCProd',
      'vAliqProd'
    ];

    $std = $this->equilizeParameters($std, $possible);
    $cofinsst = $this->dom->createElement("COFINSST");

    if (!isset($std->qBCProd)) {
      $this->dom->addChild(
        $cofinsst,
        "vBC",
        $this->conditionalNumberFormatting($std->vBC),
        false,
        "[item $std->item] Valor da Base de Cálculo da COFINS"
      );
      $this->dom->addChild(
        $cofinsst,
        "pCOFINS",
        substr($this->conditionalNumberFormatting($std->pCOFINS, 4), 0, 6),
        false,
        "[item $std->item] Alíquota da COFINS (em percentual)"
      );
    } else {
      $this->dom->addChild(
        $cofinsst,
        "qBCProd",
        $this->conditionalNumberFormatting($std->qBCProd, 4),
        false,
        "[item $std->item] Quantidade Vendida"
      );
      $this->dom->addChild(
        $cofinsst,
        "vAliqProd",
        $this->conditionalNumberFormatting($std->vAliqProd, 4),
        false,
        "[item $std->item] Alíquota da COFINS (em reais)"
      );
    }
    $this->aCOFINSST[$std->item] = $cofinsst;
    return $cofinsst;
  }

  /**
   * Grupo ISSQN U01 pai M01
   * tag CFe/infCFe/det[]/imposto/ISSQN (opcional)
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagISSQN(stdClass $std)
  {
    $possible = [
      'item',
      'vDeducISSQN',
      'vAliq',
      'cMunFG',
      'cListServ',
      'cServTribMun',
      'cNatOp',
      'indIncFisc',
    ];
    $std = $this->equilizeParameters($std, $possible);
    $issqn = $this->dom->createElement("ISSQN");

    $this->dom->addChild(
      $issqn,
      "vDeducISSQN",
      $this->conditionalNumberFormatting($std->vDeducISSQN),
      true,
      "[item $std->item] Valor da Base de Cálculo do ISSQN"
    );
    $this->dom->addChild(
      $issqn,
      "vAliq",
      str_pad($this->conditionalNumberFormatting($std->vAliq), 6, '0', STR_PAD_LEFT),
      true,
      "[item $std->item] Alíquota do ISSQN"
    );
    $this->dom->addChild(
      $issqn,
      "cMunFG",
      $std->cMunFG,
      false,
      "[item $std->item] Código do município de ocorrência do fato gerador do ISSQN"
    );
    $this->dom->addChild(
      $issqn,
      "cListServ",
      $std->cListServ,
      false,
      "[item $std->item] Item da Lista de Serviços"
    );
    $this->dom->addChild(
      $issqn,
      "cServTribMun",
      $std->cServTribMun,
      false,
      "[item $std->item] Codigo de tributação pelo ISSQN do municipio"
    );
    $this->dom->addChild(
      $issqn,
      "cNatOp",
      $std->cNatOp,
      true,
      "[item $std->item] Natureza da Operação de ISSQN"
    );
    $this->dom->addChild(
      $issqn,
      "indIncFisc",
      $std->indIncFisc,
      true,
      "[item $std->item] Indicador de Incentivo Fiscal do ISSQN"
    );
    $this->aISSQN[$std->item] = $issqn;
    return $issqn;
  }

  /**
   * Informações adicionais do produto V01 pai H01
   * tag CFe/infCFe/det[]/infAdProd
   * @param stdClass $std
   * @return DOMElement
   */
  public function taginfAdProd(stdClass $std)
  {
    $possible = ['item', 'infAdProd'];
    $std = $this->equilizeParameters($std, $possible);
    $infAdProd = $this->dom->createElement(
      "infAdProd",
      substr(trim($std->infAdProd), 0, 500)
    );
    $this->aInfAdProd[$std->item] = $infAdProd;
    return $infAdProd;
  }

  /**
   * Grupo Totais do CF-e W01 pai A01
   * tag CFe/infCFe/total
   */
  public function tagtotal(stdClass $std)
  {

    $possible = [
      'vDescSubtot',
      'vAcresSubtot',
      'vCFeLei12741'
    ];

    $std = $this->equilizeParameters($std, $possible);

    $this->total = $this->dom->createElement("total");

    $this->stdTot->vDescSubtot = round(!empty($std->vDescSubtot) ? $std->vDescSubtot : $this->stdTot->vDescSubtot, 2);
    $this->stdTot->vAcresSubtot = round(!empty($std->vAcresSubtot) ? $std->vAcresSubtot : $this->stdTot->vAcresSubtot, 2);
    $this->stdTot->vCFeLei12741 = round(!empty($std->vCFeLei12741) ? $std->vCFeLei12741 : $this->stdTot->vCFeLei12741, 2);

    $DescAcrEntr = $this->dom->createElement("DescAcrEntr");

    if ($this->stdTot->vDescSubtot > 0.0)
      $this->dom->addChild(
        $DescAcrEntr,
        "vDescSubtot",
        $this->conditionalNumberFormatting($this->stdTot->vDescSubtot, 2),
        false,
        "Valor de Entrada de Desconto sobre Subtotal"
      );

    if ($this->stdTot->vAcresSubtot > 0.0)
      $this->dom->addChild(
        $DescAcrEntr,
        "vAcresSubtot",
        $this->conditionalNumberFormatting($this->stdTot->vAcresSubtot, 2),
        false,
        "Valor de Entrada de Acréscimo sobre Subtotal"
      );

    if ($this->stdTot->vCFeLei12741 > 0.0)
      $this->dom->addChild(
        $this->total,
        "vCFeLei12741",
        $this->conditionalNumberFormatting($this->stdTot->vCFeLei12741, 2),
        false,
        "Valor aproximado dos tributos do CFe-SAT – Lei 12741/12"
      );

    // se houver desconto ou acrescimo do subtotal, adiciona o grupo
    if ($DescAcrEntr->hasChildNodes())
      $this->dom->appChild($this->total, $DescAcrEntr, "Inclusão do node vol");

    return $this->total;
  }

  /**
   * Grupo pgto Y02 pai Y01
   * tag CFe/infCFe/pgto/MP 1-10
   * @param stdClass $std
   * @return DOMElement
   */
  public function tagMP(stdClass $std)
  {

    /**
     * cMP
     *
     * 01 - Dinheiro
     * 02 - Cheque
     * 03 - Cartão de Crédito
     * 04 - Cartão de Débito
     * 05 - Crédito Loja
     * 10 - Vale Alimentação
     * 11 - Vale Refeição
     * 12 - Vale Presente
     * 13 - Vale Combustível
     * ------------------------------------
     * A partir de 01/09/2021
     * 15=Boleto Bancário
     * 16=Depósito Bancário
     * 17=Pagamento Instantâneo (PIX)
     * 18=Transferência bancária, Carteira Digital
     * 19=Programa de fidelidade, Cashback, Crédito Virtual
     * 90= Sem pagamento
     */
    $possible = [
      'cMP',
      'vMP',
      'cAdmC',
    ];

    $std = $this->equilizeParameters($std, $possible);
    $MP = $this->dom->createElement("MP");

    if (!$this->pgto)
      $this->pgto = $this->dom->createElement('pgto');

    $identificador = 'WA01 - WA02 - <MP>';

    $this->dom->addChild(
      $MP,
      "cMP",
      $std->cMP,
      true,
      $identificador . "Código do Meio de Pagamento empregado para quitação do CF-e"
    );
    $this->dom->addChild(
      $MP,
      "vMP",
      $this->conditionalNumberFormatting($std->vMP),
      true,
      $identificador . "Valor do Meio de Pagamento empregado para quitação do CF-e"
    );
    $this->dom->addChild(
      $MP,
      "cAdmC",
      $std->cAdmC,
      false,
      $identificador . "Credenciadora de cartão de débito ou crédito"
    );

    $this->dom->appChild($this->pgto, $MP);

    return $MP;
  }

  /**
   * Grupo de Informações Adicionais Z01 pai A01
   * tag CFe/infCFe/infAdic (opcional)
   * @param stdClass $std
   * @return DOMElement
   */
  public function taginfAdic(stdClass $std)
  {
    $possible = [
      'infCpl'
    ];

    $std = $this->equilizeParameters($std, $possible);
    $this->buildInfAdic();

    $this->dom->addChild(
      $this->infAdic,
      "infCpl",
      $std->infCpl,
      false,
      "Informações Complementares de interesse do Contribuinte"
    );
    return $this->infAdic;
  }

  /**
   * Tag raiz do CFe
   * tag CFe DOMNode
   * Função chamada pelo método [ monta ]
   *
   * @return DOMElement
   */
  protected function buildCFe()
  {
    if (empty($this->CFe)) {
      $this->CFe = $this->dom->createElement('CFe');
    }

    return $this->CFe;
  }

  /**
   * Insere dentro da tag det os produtos
   * tag CFe/infCFe/det[]
   * @return array|string
   */
  protected function buildDet()
  {
    if (empty($this->aProd)) {
      return '';
    }

    //montagem da tag imposto[]
    foreach ($this->aImposto as $nItem => $imposto) {
      if (!empty($this->aICMS[$nItem])) {
        $this->dom->appChild($imposto, $this->aICMS[$nItem], 'Inclusão do node ICMS');
      }
      if (!empty($this->aISSQN[$nItem])) {
        $this->dom->appChild($imposto, $this->aISSQN[$nItem], 'Inclusão do node ISSQN');
      }
      if (!empty($this->aPIS[$nItem])) {
        $this->dom->appChild($imposto, $this->aPIS[$nItem], 'Inclusão do node PIS');
      }
      if (!empty($this->aPISST[$nItem])) {
        $this->dom->appChild($imposto, $this->aPISST[$nItem], 'Inclusão do node PISST');
      }
      if (!empty($this->aCOFINS[$nItem])) {
        $this->dom->appChild($imposto, $this->aCOFINS[$nItem], 'Inclusão do node COFINS');
      }
      if (!empty($this->aCOFINSST[$nItem])) {
        $this->dom->appChild($imposto, $this->aCOFINSST[$nItem], 'Inclusão do node COFINSST');
      }

      $this->aImposto[$nItem] = $imposto;
    }

    //montagem da tag det[]
    foreach ($this->aProd as $nItem => $prod) {
      $det = $this->dom->createElement('det');
      $det->setAttribute('nItem', $nItem);

      //insere aobsFiscoDet
      if (!empty($this->aobsFiscoDet[$nItem]) && is_array($this->aobsFiscoDet[$nItem])) {

        foreach ($this->aobsFiscoDet[$nItem] as $obsFiscoDet) {
          $this->dom->appChild($prod, $obsFiscoDet, 'Inclusão do node aobsFiscoDet');
        }
      }

      $det->appendChild($prod);

      //insere imposto
      if (!empty($this->aImposto[$nItem])) {
        $child = $this->aImposto[$nItem];
        $this->dom->appChild($det, $child, 'Inclusão do node imposto');
      }

      //insere infAdProd
      if (!empty($this->aInfAdProd[$nItem])) {
        $child = $this->aInfAdProd[$nItem];
        $this->dom->appChild($det, $child, 'Inclusão do node infAdProd');
      }

      $this->aDet[] = $det;
      $det = null;
    }

    return $this->aProd;
  }

  /**
   * Grupo de Informações Adicionais Z01 pai A01
   * tag CFe/infCFe/infAdic (opcional)
   * Função chamada pelos metodos
   * [taginfAdic] [tagobsCont] [tagprocRef]
   * @return DOMElement
   */
  protected function buildInfAdic()
  {
    if (empty($this->infAdic)) {
      $this->infAdic = $this->dom->createElement('infAdic');
    }

    return $this->infAdic;
  }

  /**
   * Retorna os erros detectados
   * @return array
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Includes missing or unsupported properties in stdClass
   * Replace all unsuported chars
   * @param stdClass $std
   * @param array $possible
   * @return stdClass
   */
  protected function equilizeParameters(stdClass $std, $possible)
  {
    return Strings::equilizeParameters(
      $std,
      $possible,
      $this->replaceAccentedChars
    );
  }

  /**
   * Formatação numerica condicional
   * @param string|float|int|null $value
   * @param int $decimal
   * @return string
   */
  protected function conditionalNumberFormatting($value = null, $decimal = 2)
  {
    if (is_numeric($value)) {
      return number_format($value, $decimal, '.', '');
    }
    return null;
  }
}

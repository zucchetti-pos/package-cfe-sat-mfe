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
use stdClass;
use RuntimeException;
use DOMElement;
use NFePHP\Common\Strings;

class MakeCancelamento
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
   * @var \NFePHP\Common\DOMImproved
   */
  public $dom;
  /**
   * @var DOMElement
   */
  protected $CFeCanc;
  /**
   * @var DOMElement
   */
  protected $infCFe;
  /**
   * @var DOMElement
   */
  protected $ide;

  /**
   * Função construtora cria um objeto DOMDocument
   * que será carregado com o documento fiscal
   */
  public function __construct()
  {
    $this->dom = new Dom('1.0', 'UTF-8');
    $this->dom->preserveWhiteSpace = false;
    $this->dom->formatOutput = false;
  }

  /**
   * Returns xml string and assembly it is necessary
   * @return string
   */
  public function getXML()
  {
    if (empty($this->xml)) {
      $this->montaCFeCanc();
    }

    return $this->xml;
  }

  /**
   * Call method of xml assembly. For compatibility only.
   * @return string
   */
  public function montaCFeCanc()
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

    // cria a tag raiz do CFeCanc
    $this->buildCFeCanc();

    if (!$this->ide) {
      $this->tagide(new \stdClass);
    }

    // tag ide
    $this->dom->appChild($this->infCFe, $this->ide, 'Falta tag "infCFe"');

    // tag emit
    $this->dom->appChild($this->infCFe, $this->tagEmit(), 'Falta tag "infCFe"');

    // tag dest
    $this->dom->appChild($this->infCFe, $this->tagdest(), 'Falta tag "infCFe"');

    // tag total
    $this->dom->appChild($this->infCFe, $this->tagTotal(), 'Falta tag "infCFe"');

    // tag infAdic
    $this->dom->appChild($this->infCFe, $this->taginfAdic(), 'Falta tag "infCFe"');

    // tag infCFe
    $this->dom->appChild($this->CFeCanc, $this->infCFe, 'Falta tag "CFeCanc"');

    // tag CFeCanc
    $this->dom->appendChild($this->CFeCanc);

    // Salva o xml
    $this->xml = $this->dom->saveXML();

    if (count($this->errors) > 0) {
      throw new RuntimeException('Existem erros nas tags. Obtenha os erros com getErrors().');
    }

    // Retorna o xml
    return $this->xml;
  }

  /**
   * Tag raiz do CFeCanc
   * tag CFeCanc DOMNode
   * Função chamada pelo método [ monta ]
   *
   * @return DOMElement
   */
  protected function buildCFeCanc()
  {
    if (empty($this->CFeCanc)) {
      $this->CFeCanc = $this->dom->createElement('CFeCanc');
    }

    return $this->CFeCanc;
  }

  /**
   * Informações do CFe A01 pai CFeCanc
   * tag CFeCanc/infCFe
   * @param  stdClass $std
   * @return DOMElement
   */
  public function taginfCFe(stdClass $std)
  {
    $possible = [
      'chCanc',
    ];

    $std = $this->equilizeParameters($std, $possible);

    $this->infCFe = $this->dom->createElement("infCFe");

    $this->infCFe->setAttribute(
      "chCanc",
      $std->chCanc
    );

    return $this->infCFe;
  }

  /**
   * Informações de identificação da NF-e B01 pai A01
   * tag CFeCanc/infCFe/ide
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
   * tag CFeCanc/infCFe/emit
   * @return DOMElement
   */
  private function tagemit()
  {
    return $this->dom->createElement("emit");
  }

  /**
   *
   * Identificação do destinatário do CF-e E01 pai A01
   * tag CFeCanc/infCFe/dest
   * @return DOMElement
   */
  private function tagdest()
  {
    return $this->dom->createElement("dest");
  }

  /**
   *
   * Grupo de Informações Adicionais do CF-e Z01 pai A01
   * tag CFeCanc/infCFe/infAdic
   * @return DOMElement
   */
  private function taginfAdic()
  {
    return $this->dom->createElement("infAdic");
  }

  /**
   * Grupo Totais do CF-e W01 pai A01
   * tag CFeCanc/infCFe/total
   * @return DOMElement
   */
  private function tagtotal()
  {
    return $this->dom->createElement("total");
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
      false
    );
  }
}

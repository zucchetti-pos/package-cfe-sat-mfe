<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\CfeSatMfe\Make;

try {

  $make = new Make();

  $std = new \stdClass;
  $std->versao = '0.07';
  $make->taginfCFe($std);

  $std = new \stdClass;
  $std->CNPJ = '12345678909123';
  $std->numeroCaixa = '000';
  $std->signAC = '1';
  $make->tagide($std);

  $std = new \stdClass;
  $std->CNPJ = '11111111111111';
  $std->IE = '111111111111';
  $std->IM = '123123';
  $std->cRegTribISSQN = '1';
  $std->indRatISSQN = 'N';
  $make->tagemit($std);

  $std = new \stdClass;
  $std->xLgr = 'Rua exemplo';
  $std->nro = 'S/N';
  $std->xBairro = 'Centro';
  $std->xMun = 'São Paulo';
  $std->UF = 'SP';
  // $make->tagentrega($std);

  $std = new \stdClass;
  // $std->CPF = '99999999991';
  // $std->xNome = 'Consumidor Final';
  $make->tagdest($std);

  $prods = [
    [
      'item' => 1,
      'cProd' => '000001',
      'cEAN' => 'SEM GTIN',
      'xProd' => 'Coca Cola 350ml',
      'NCM' => '73181500',
      'CFOP' => '0001',
      'uCom' => 'UN',
      'qCom' => '1',
      'vUnCom' => 0.01,
      'indRegra' => 'A',

      'imposto' => [
        'item' => 1,
        'vItem12741' => 0.0
      ],

      'ICMS' => [
        'item' => 1,
        'Orig' => '0',
        'CST' => '00',
        // 'CSOSN' => '500',
        'pICMS' => 0.0,
      ],

      'PIS' => [
        'item' => 1,
        'CST' => '99',
        'vBC' => 0.0,
        'pPIS' => 0,
        // 'vAliqProd' => 0,
        // 'qBCProd' => '',
      ],

      'COFINS' => [
        'item' => 1,
        'CST' => '99',
        'vBC' => 0,
        'pCOFINS' => 0,
        // 'qBCProd' => '',
        // 'vAliqProd' => '',
      ],

      'obsFiscoDet' => [
        [
          'item' => 1,
          'xCampoDet' => '04.06.05.04',
          'xTextoDet' => 'Comete crime quem sonega'
        ],
        [
          'item' => 1,
          'xCampoDet' => 'Teste',
          'xTextoDet' => 'Mensagem lllll'
        ]
      ]
    ]
  ];

  foreach ($prods as $prod) {
    $prod = json_decode(json_encode($prod));

    $make->tagprod($prod);

    if ($prod->imposto)
      $make->tagimposto($prod->imposto);

    $make->tagICMS($prod->ICMS);
    $make->tagPIS($prod->PIS);
    $make->tagCOFINS($prod->COFINS);


    foreach ($prod->obsFiscoDet ?? [] as $obsFisco) {

      $make->tagobsFiscoDet($obsFisco);
    }
  }

  $pags = [
    [
      'cMP' => '01',
      'vMP' => 0.01
    ],

  ];

  foreach ($pags as $mp) {
    $mp = json_decode(json_encode($mp));
    $make->tagMP($mp);
  }

  $make->setOnlyAscii(true);
  $xml = $make->getXML();

  header("Content-type: text/xml");
  die($xml);
} catch (\Exception $e) {
  if ($make->getErrors()) {
    echo '<pre>';
    print_r($make->getErrors());
    echo '</pre>';
  } else {

    echo $e;
  }
}

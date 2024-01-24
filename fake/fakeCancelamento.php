<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\CfeSatMfe\MakeCancelamento;

try {

  $make = new MakeCancelamento();

  $std = new \stdClass;
  $std->chCanc = 'CFe35130159596908000152599000002110000012361207';
  $make->taginfCFe($std);

  $std = new \stdClass;
  $std->CNPJ = '12345678909123';
  $std->numeroCaixa = '000';
  $std->signAC = 'dasdsadasdasd';
  $make->tagide($std);

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

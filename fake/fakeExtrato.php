<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\CfeSatMfe\DA\Extrato;

try {

  $extrato = new Extrato(file_get_contents('./cfe-sat-0.07.xml'));
  // $extrato = new Extrato(file_get_contents('./cfe-sat-0.08.xml'));
  // $extrato = new Extrato(file_get_contents('./cfe-sat-cancelado.xml'));
  // $extrato = new Extrato(file_get_contents('./cfe-sat-cancelado-cliente.xml'));
  // $extrato = new Extrato(file_get_contents('./cfe-sat-pagamentos.xml'));
  $extrato = new Extrato(file_get_contents('./cfe-sat-layout.xml'));
  $extrato = new Extrato(file_get_contents('./cfe-sat-100itens.xml'));
  $extrato = new Extrato(file_get_contents('./cfe-sat-500itens.xml'));

  // $logo = '';
  $logo = 'exemplo-logo.png';

  // $extrato->setPaperWidth(58);

  $render = $extrato->render($logo);

  header("Content-type: application/pdf");

  die($render);
} catch (\Exception $e) {

  die($e);
}

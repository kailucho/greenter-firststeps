<?php

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Response\BillResult;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\See;

require __DIR__.'/vendor/autoload.php';

/**@var $see See*/
$see = require __DIR__.'/config.php';

// Cliente
$client = new Client();
$client->setTipoDoc('6')
    ->setNumDoc('20000000001')
    ->setRznSocial('EMPRESA 1');

// Emisor
$address = new Address();
$address->setUbigueo('150101')
    ->setDepartamento('LIMA')
    ->setProvincia('LIMA')
    ->setDistrito('LIMA')
    ->setUrbanizacion('NONE')
    ->setDireccion('AV LS');

$company = new Company();
$company->setRuc('20000000001')
    ->setRazonSocial('EMPRESA SAC')
    ->setNombreComercial('EMPRESA')
    ->setAddress($address);

// Venta
$invoice = (new Invoice())
    ->setUblVersion('2.1')
    ->setTipoOperacion('0101') // Catalog. 51
    ->setTipoDoc('01')
    ->setSerie('F001')
    ->setCorrelativo('1')
    ->setFechaEmision(new DateTime())
    ->setTipoMoneda('PEN')
    ->setClient($client)
    ->setMtoOperGravadas(100.00)
    ->setMtoIGV(18.00)
    ->setTotalImpuestos(18.00)
    ->setValorVenta(100.00)
    ->setMtoImpVenta(118.00)
    ->setCompany($company);

$item = (new SaleDetail())
    ->setCodProducto('P001')
    ->setUnidad('NIU')
    ->setCantidad(2)
    ->setDescripcion('PRODUCTO 1')
    ->setMtoBaseIgv(100)
    ->setPorcentajeIgv(18.00) // 18%
    ->setIgv(18.00)
    ->setTipAfeIgv('10')
    ->setTotalImpuestos(18.00)
    ->setMtoValorVenta(100.00)
    ->setMtoValorUnitario(50.00)
    ->setMtoPrecioUnitario(59.00);

$legend = (new Legend())
    ->setCode('1000')
    ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

$invoice->setDetails([$item])
        ->setLegends([$legend]);

$result = $see->send($invoice);

// Guardar XML
file_put_contents($invoice->getName().'.xml',
                  $see->getFactory()->getLastXml());

if (!$result->isSuccess()) {
    var_dump($result->getError());
    exit();
}

/**@var $result BillResult*/
echo $result->getCdrResponse()->getDescription();

// Guardar CDR
file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());
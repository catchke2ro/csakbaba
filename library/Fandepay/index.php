<?php 
require 'autoload.php';
Autoloader::Register();

//var_dump(Autoloader::Load('Address'));


//$add = new Fandepay\Api\Model\Address;

//$add->setLabel('akármi');

//echo $add->getLabel();
        
        $data = array();

        $data['payment_id'] = '';

 
        $customer = new Fandepay\Api\Model\Customer();

        $customer->setType(Fandepay\Api\Model\Customer::TYPE_COMPANY);
        //$customer->setId(52);
        $customer->setName('Berta6 Márton6');
        $customer->setEmail('bertamarton+apitestcust6@gmail.com');
        $customer->setFiscalcode('1111111116');



        $addr = new Fandepay\Api\Model\Address;
        $addr->setType(Fandepay\Api\Model\Address::TYPE_HEAD);
        $addr->setLabel('Számlázási cím');
        $addr->setCountryCode('HU');
        $addr->setRegion('Budapes4');
        $addr->setPostalCode('1112');
        $addr->setCity('Budapes4');
        $addr->setAddressLine('Akárm4 utca 4');

        $customer->addAddress($addr);

        $data['customer'] = $customer;

        $invoice = new Fandepay\Api\Model\Invoice();
        $invoice->setType('INVOICE');
        $invoice->setDate('2014-01-21');
        $invoice->setFulfillmentDate('2014-01-21');
        $invoice->setPaymentDeadline('2014-01-21');
        //$invoice->setPaymode('CASH_ON_DELIVERY');
        //$invoice->setPaymentStatus('PAID');
        $invoice->setSendEmail(0);

        $item = new Fandepay\Api\Model\InvoiceItem(); 


        $item->setName('Valami 4');
        $item->setQuantity(1);        
        $item->setUnit('db');
        $item->setVatKey(27);
        $item->setAmountUnit(100);


        $invoice->addItem($item);
        
        $data['invoice'] = $invoice;
        $data['payment_id'] = mt_rand(5000, 6000);
        $endpoint = new Fandepay\Api\Endpoints\Pay($data['payment_id'], $data['customer'], $data['invoice']);
        //$apiCfg = $app['config']['api'];
        $cfg = $endpoint->getConfig();
        //$cfg['api_baseurl'] = $apiCfg['url'];
        //$cfg['key'] = $apiCfg['key'];
        //$cfg['secret'] = $apiCfg['secret'];
        var_dump($endpoint->getUrl());

        try {
            $result = $endpoint->curl();

        $invoice = new Fandepay\Api\Model\Invoice(array(
            'type' => Fandepay\Api\Enum\InvoiceType::INVOICE, 
            'payment_id' => $data['payment_id']
        ));
        $endpoint = new Fandepay\Api\Endpoints\InvoiceSearch($invoice);

            $res = $endpoint->getResult($result, false);
            var_dump($res);
            //return $app->redirect($result['pay_url']);
        } catch (ApiErrorException $e) {
            $error = $e->getMessage();
            $errors = $e->getDetails();

            var_dump($error);
        }


?>


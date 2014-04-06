<?php 

require 'autoload.php';
Autoloader::Register();

        $invoice = new Fandepay\Api\Model\Invoice(array(
            'type' => Fandepay\Api\Enum\InvoiceType::INVOICE, 
            'payment_id' => '5774'
        ));


		$endpoint = new Fandepay\Api\Endpoints\InvoiceSearch($invoice);

         try {
            $result = $endpoint->getResult(null, true);
            if($result['result'] == 'ok'){
                var_dump($result['invoice']->getNumber());    
            }
            
            //return $app->redirect($result['pay_url']);
        } catch (ApiErrorException $e) {
            $error = $e->getMessage();
            $errors = $e->getDetails();

            var_dump($error);
        }

?>
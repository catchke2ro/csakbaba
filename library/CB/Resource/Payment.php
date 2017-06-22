<?php
class CB_Resource_Payment {

	/**
	 * @var CB_Controller_Action
	 */
	public $controller;
	/**
	 * @var \CB\Payment
	 */
	public $payment;

	/**
	 * @var CB\Model\Payment
	 */
	public $paymentModel;

	private $_barionconfig;

	private $_barionTest=false;

	private $_billingoconfig;

	static $statusCodes=array(
		1=>'Fizetésre vár', 2=>'Fizetve', 3=>'Sikertelen fizetés'
	);

	static $paymentTypes=array(
		'CREDIT_CARD'=>'Bankkártyás fizetés', 'BANK_TRANSFER'=>'Átutalás'
	);


	public function __construct($pid=null, $controller=null){
		$this->paymentModel=new \CB\Model\Payment();
		$this->controller=$controller;
		if(!is_null($pid) && ($payment=$this->paymentModel->findOne(['conditions'=>['OR'=>['pid'=>$pid, 'bpid'=>$pid]]]))){
			$this->payment=$payment;
		}

		$this->_barionconfig=Zend_Registry::get('CsbConfig')->get('barion');
		$this->_billingoconfig=Zend_Registry::get('CsbConfig')->get('billingo');
	}


	public function newPayment($data){
		$payment=new \CB\Payment();
		$payment->saveAll($data);
		if(!$payment->user) $payment->user=Zend_Registry::get('user');
		$payment->date=date('Y-m-d H:i:s');
		$payment->pid=uniqid('CSB');
		if(!$payment->status) $payment->status=1;
		if(!$payment->type) $payment->type='CREDIT_CARD';
		$this->payment=$this->paymentModel->save($payment);
	}


	public function doPayment(){
		/*$this->_billingoInvoice();
	die();*/
		//$redirectUri=$this->_invoice();
		$redirectUri=$this->_start();

		if($redirectUri){
			$this->controller->redirect($redirectUri);
		}
	}


	public function redirect(){
		//$ok=$_GET['status']=='ACTIVE' ? true : false;
		$barion=new \Barion\Barion($this->_barionconfig->get('posKey'));
		if($this->_barionTest){
			$barion->setApiUrl('https://api.test.barion.com/v2');
			$barion->setBarionRedirectUrl('https://test.barion.com/pay?id=');
		}

		$payment=new \Barion\Payment\SimplePayment($barion, $this->payment->pid);
		$paymentStateResponse=$payment->getPaymentState($this->payment->bpid);

		$ok=in_array($paymentStateResponse->getStatus(), ['Succeeded','Prepared']);
		if($ok){
			CB_Resource_Functions::logEvent('userChargeSuccess', array('payment'=>$this->payment));
			$this->controller->m('A fizetés sikeres volt. Az egyenlegeden pillanatokon belül látható lesz az összeg. Számládat hamarosan megtekintheted lejjebb, valamint kiküldjük e-mailben', 'message');
			$this->controller->redirect($this->controller->url('egyenleg'));
		} else {
			CB_Resource_Functions::logEvent('userChargeFailed', array('payment'=>$this->payment));
			$this->payment->status=3;
			$this->paymentModel->save($this->payment, true);
			$this->controller->m('Sikertelen fizetés', 'error');
			$this->controller->redirect($this->controller->url('egyenleg'));
		}
	}

	public function hook(){
		/*if($_GET['type']=='invoice'){
			l('INVOICEIPN');
			$ipn = new Fandepay\Api\Webhooks\Invoice();
			echo 'SUCCESS '.$ipn->getToken();
			$invoiceData=array( 'invoice_number'=>$ipn->getInvoiceNumber(), 'invoice_type'=>$ipn->getInvoiceType(),	'payment_status'=>$ipn->getPaymentStatus(), 'pdf_url'=>$ipn->getPdfUrl() );
			$this->payment->invoice_data=$invoiceData;
			$this->paymentModel->save($this->payment, true);
		}

		if($_GET['type']=='payment'){
			l('IPN');
			$ipn = new Fandepay\Api\Webhooks\Ipn();
			echo 'SUCCESS '.$ipn->getToken();
			$invoiceData=array('invoice_number'=>$ipn->getInvoiceNumber(), 'invoice_type'=>$ipn->getInvoiceType(), 'payment_status'=>$ipn->getInvoice()->getPaymentStatus(), 'pdf_url'=>$ipn->getPdfUrl() );
			$this->payment->status=2;
			$this->payment->invoice_data=$invoiceData;
			$this->paymentModel->save($this->payment, true);
			$this->_setInvoiceStatus(2);
			$this->_userBalance();
			$this->controller->emails->charged(array('user'=>$this->payment->user, 'payment'=>$this->payment));

		}

		$client=new Zend_Http_Client($ipn->getPdfUrl());
		$client->setAdapter(new Zend_Http_Client_Adapter_Curl());
		$response=$client->request();
		if($response->isSuccessful() && $response->getBody()) file_put_contents(APPLICATION_PATH.'/../tmp/invoices/'.str_replace('/', '_', $ipn->getInvoiceNumber()).'.pdf', $response->getBody());*/

		$barion=new \Barion\Barion($this->_barionconfig->get('posKey'));

		if($this->_barionTest){
			$barion->setApiUrl('https://api.test.barion.com/v2');
			$barion->setBarionRedirectUrl('https://test.barion.com/pay?id=');
		}



		$payment=new \Barion\Payment\SimplePayment($barion, $this->payment->pid);
		$paymentStateResponse=$payment->getPaymentState($this->payment->bpid);
		$this->payment->barion_data=$paymentStateResponse->rawResponse;

		if($paymentStateResponse->getStatus()=='Succeeded' && $this->payment->status != 2){
			$this->payment->status=2;
			$this->_userBalance();
			try{
				$this->_billingoInvoice();
			} catch(Exception $e){
			
			}
			$this->controller->emails->charged(array('user'=>$this->payment->user, 'payment'=>$this->payment));
		}
		$this->paymentModel->save($this->payment);



	}


	public function _userBalance(){
		$userId=$this->payment->user->get()->id;
		$userModel=new \CB\Model\User();
		if(($user=$userModel->findOneById($userId))){
			CB_Resource_Functions::logEvent('userChargeSuccessBalance', array('payment'=>$this->payment));
			$user->balance=$user->balance+intval($this->payment->amount);
			$userModel->save($user);
		}

	}

	public function _start(){
		$this->payment->barion_data=[];

		$barion=new \Barion\Barion($this->_barionconfig->get('posKey'));
		if($this->_barionTest){
			$barion->setApiUrl('https://api.test.barion.com/v2');
			$barion->setBarionRedirectUrl('https://test.barion.com/pay?id=');
		}

		$payment=new \Barion\Payment\SimplePayment($barion, $this->payment->pid);

		$transaction=new \Barion\Payment\Transaction([
			'TransactionId'=>$this->payment->pid,
			'Payee'=>$this->_barionconfig->get('payeeEmail'),
			'Total'=>round(intval($this->payment->amount)),
			'Comment'=>'csakbaba.hu egyenleg feltöltés'
		]);

		$item=new \Barion\Payment\Item([
			'Name'=>'csakbaba.hu egyenleg feltöltés',
			'Description'=>'csakbaba.hu egyenleg feltöltés',
			'Quantity'=>1,
			'Unit'=>'db',
			'UnitPrice'=>$this->payment->amount,
			'ItemTotal'=>$this->payment->amount
		]);
		$transaction->addItem($item);
		$startResponse=$payment->startPayment([$transaction]);

		if(!$startResponse->isOK()){
			CB_Resource_Functions::logEvent('barionError', array('errors'=>$startResponse->getErrors(), 'pid'=>$this->payment->pid));
			return false;
		}

		$paymentStateResponse=$payment->getPaymentState($startResponse->getBarionPaymentId());

		$this->payment->bpid=$startResponse->getBarionPaymentId();
		$this->payment->barion_data=$paymentStateResponse->rawResponse;
		$this->paymentModel->save($this->payment);


		return $startResponse->getRedirectUrl($barion);
	}


	public function _billingoInvoice(){
		$b = new \Billingo\API\Connector\HTTP\Request([
			'public_key' => $this->_billingoconfig->get('publicKey'),
			'private_key' => $this->_billingoconfig->get('privateKey'),
		]);

		$address = $this->payment->user->get()->getInvoiceAddress();
		$newClient = $b->post('clients', array(
			'name' => $address['name'],
			'email' => $this->payment->user->email,
			'billing_address' => array(
				'street_name' => $address['street'],
				'city' => $address['city'],
				'postcode' => $address['zip'],
				'country' => 'Magyarország',
			)
		));
		
		
		$this->payment->user->billingoid=$newClient['id'];
		$userModel=new \CB\Model\User();
		$userModel->save($this->payment->user, true);


		$clientId=$this->payment->user->billingoid;

		//$paymentMethods=$b->getPaymentMethods('hu');

		$price=floatval($this->payment->amount);
		$vat=($price*0.27/(1.27));

		$product = array(
			'client_uid' => $clientId,
			'fulfillment_date' => date('Y-m-d'),
			'due_date' => date('Y-m-d'),
			'payment_method' => 5,
			'comment' => 'N/A',
			'currency' => 'HUF',
			'template_lang_code' => 'hu',
			'electronic_invoice' => 1,
			'block_uid' =>0,
			'round_to'=>1,
			'type' =>3,
			'items' =>
				array (
						array(
							'description' => 'csakbaba.hu egyenleg feltöltés',
							'net_unit_price' => ($price - $vat),
							'qty' => 1,
							'unit' => 'db',
							'vat_id' => 1,
						),
				),
		);

		$invoice = $b->post('invoices', $product);
		
		
		$invoiceData=array(
			'invoice_number'=>$invoice['attributes']['invoice_no'],
			'id'=>$invoice['id']
		);
		$this->payment->invoice_data=$invoiceData;
		$this->paymentModel->save($this->payment, true);
		
		$pdf = $b->downloadInvoice($invoiceData['id']);

		$pdfContent = $pdf->getContents();
		file_put_contents(APPLICATION_PATH.'/../tmp/invoices/'.str_replace('/', '_', $invoiceData['invoice_number']).'.pdf', $pdfContent);

	}

	public function _invoice($invoiceType='PREINVOICE'){
		$data = array();

		$customer = new Fandepay\Api\Model\Customer();
		$customer->setType(Fandepay\Api\Model\Customer::TYPE_PERSON);
		$customer->setName($this->payment->user->getInvoiceAddress()['name']);
		$customer->setEmail($this->payment->user->email);
		$customer->setBankaccountnumber($this->payment->user->getInvoiceAddress()['address_banknum']);

		$szamladdr = new Fandepay\Api\Model\Address;
		$szamladdr->setType(Fandepay\Api\Model\Address::TYPE_HEAD);
		$szamladdr->setLabel('Számlázási cím');
		$szamladdr->setCountryCode('HU');
		$szamladdr->setPostalCode($this->payment->user->getInvoiceAddress()['zip']);
		$szamladdr->setCity($this->payment->user->getInvoiceAddress()['city']);
		$szamladdr->setAddressLine($this->payment->user->getInvoiceAddress()['street']);


		$customer->addAddress($szamladdr);

		$invoice = new Fandepay\Api\Model\Invoice();
		$invoice->setType($invoiceType);
		$invoice->setDate(date('Y-m-d'));
		$invoice->setFulfillmentDate(date('Y-m-d'));
		$invoice->setPaymentDeadline(date('Y-m-d'));
		$invoice->setPaymode($this->payment->type);
		$invoice->setPaymentStatus($this->payment->status!=2 ? 'NOT_PAID' : 'PAID');
		$invoice->setSendEmail(0);
		$invoice->setCurrency('HUF');

		$price=floatval($this->payment->amount);
		$vat=ceil(($price*0.27/(1.27))*100)/100;
		$item=new Fandepay\Api\Model\InvoiceItem();
		$item->setName('csakbaba.hu egyenleg feltöltés')->setAmountUnit($price-$vat)->setQuantity(1)->setVatKey(27)->setUnit('db');
		//$item->setName('csakbaba.hu egyenleg feltöltés')->setAmountUnit(1)->setQuantity(1)->setVatKey(27)->setUnit('db');
		$invoice->addItem($item);

		$data['customer'] = $customer;
		$data['invoice'] = $invoice;
		$data['payment_id'] = $this->payment->pid;
		$endpoint = new Fandepay\Api\Endpoints\Pay($data['payment_id'], $data['customer'], $data['invoice']);

		try {
			$result = json_decode($endpoint->curl(), true);
			if(empty($result['pay_url'])){
				l('FANDEPAYINVOICE: '.print_r($result, true), 'err');
				return false;
			}
			return $result['pay_url'];
		} catch (ApiErrorException $e) {
			$error = $e->getMessage();
			$errors = $e->getDetails();
			l($error);
		}
		return false;
	}


	private function _setInvoiceStatus($status){
		$invoice = new Fandepay\Api\Model\Invoice(array(
			'type' => Fandepay\Api\Enum\InvoiceType::PREINVOICE,
			'payment_id' => $this->payment->id
		));
		$endpoint = new Fandepay\Api\Endpoints\InvoiceSearch($invoice);
		$result = $endpoint->getResult();
		if($result['result']=='ok'){
			$update=new \Fandepay\Api\Endpoints\InvoiceUpdatePaymentstatus($result['invoice'], $status);
			$updateResult=$update->getResult();
		}
	}








	private function _fandepayRedirect(){
		$ok=(!empty($_GET['status']) && $_GET['status']=='ACTIVE');
		$this->order->status=$ok ? 3 : 2;
		$this->orderModel->save($this->order);
		return $ok;
	}

	private function _fandepayHook(){
		}


	public function paymenthookAction(){
		$this->getHelper('viewRenderer')->setNoRender(true);
		$this->getHelper('layout')->disableLayout();
		l('hook: '.print_r(array('get'=>$_GET, 'post'=>$_POST), true));
		if(empty($_GET['p']) || empty($_GET['type']) || empty($_POST)) return;


		$this->getHelper('viewRenderer')->setNoRender(true);
		$this->getHelper('layout')->disableLayout();

	}




	static function fandepayRedirectValidate(){
		return !(empty($_GET['p']) || empty($_GET['ref']));
	}
	static function fandepayHookValidate(){
		return !(empty($_GET['p']) || empty($_POST['payment_id']));
	}

}

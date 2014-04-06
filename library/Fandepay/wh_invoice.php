<?php 
require 'autoload.php';
Autoloader::Register();

$file = 'invoicelog_'.time().'.txt';
$file2 = 'invoicepostlog_'.time().'.txt';

if (isset($_POST)) {
    
    file_put_contents($file2, var_export($_POST, TRUE), FILE_APPEND | LOCK_EX); 

}

file_put_contents($file, 'IPN:'."\n", FILE_APPEND | LOCK_EX);

try {
    $ipn = new Fandepay\Api\Webhooks\Invoice();
    file_put_contents($file, 'IPN1:'."\n", FILE_APPEND | LOCK_EX);
    file_put_contents($file, var_export($ipn->getInvoice(), TRUE), FILE_APPEND | LOCK_EX);
    header('HTTP/1.0 200 Ok');
    $out = 'SUCCESS '.$_POST['token'];
    echo $out;
    //syslog(LOG_INFO, var_export($ipn->getData(), true));
} catch (Exception $e) {
    //var_dump('Error: '. $e->getMessage());
    //syslog(LOG_ERR, $e->getMessage());
    file_put_contents($file, $e->getMessage(), FILE_APPEND | LOCK_EX);
}
?>
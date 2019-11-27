<?php
ini_set('error_reporting', E_ALL);

$cert="/var/www/vidal/web/sert/testSSLClient.pem"; //Сертификат
$wsdl="https://mosmedzdrav.ru:10002/emias-soap-service/PGUServicesInfo2?wsdl"; //Адрес wdsl сервиса
$pass = 'testSSLClient';
if (!is_file($cert)){
    echo 'file certificate not found!';
    exit;
}
$sslOptions = array(
    'ssl' => array(
        'cafile' => "/var/www/vidal/web/sert/RootMedCA.cer",
        'allow_self_signed' => true,
        'verify_peer' => false,
    ),
);
$sslContext = stream_context_create($sslOptions);
$sp = new SoapClient($wsdl ,array(
    'local_cert' => $cert,
    'passphrase'    => $pass,
    'stream_context' => $sslContext,
    'trace' => 0,
    'exceptions' => 0,
    'cache_wsdl' => WSDL_CACHE_NONE,
    'wsdl_cache_enabled' => false
));


    $omsNumber = 'R25090000002789';
    $omsSeries = '';
    $birthDate = new \DateTime('1983-08-17');
    $externalSystemId = 'MPGU';

    $data = $sp->__getFunctions();
    var_dump($data);
    echo '<hr />';
    $data = $sp->__getTypes();
    var_dump($data);
    echo '<hr />';

    var_dump($data);
    echo '<hr />';

    $date = new \DateTime('1987-02-06');
//    var_dump($date);
//    exit;

    try{
        $data = $sp->getSpecialitiesInfo(array('omsNumber'=>'9988889785000068', 'birthDate'=>'2011-04-14T00:00:00', 'externalSystemId'=>'MPGU'));
        var_dump($data);
        echo '<hr />';

        $data = $sp->getDoctorsInfo(array('omsNumber'=>'9988889785000068', 'birthDate'=>'2011-04-14T00:00:00','specialityId'=>6, 'externalSystemId'=>'MPGU'));
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        echo '<hr />';

        $data = $sp->getAvailableResourceScheduleInfo(array('omsNumber'=>'9988889785000068', 'birthDate'=>'2011-04-14T00:00:00','availableResourceId'=>11034423,'complexResourceId'=>10315080, 'externalSystemId'=>'MPGU'));
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        echo '<hr />';



        //        $data = $sp->getAllLpusInfo(array('returnBranches' => false, 'externalSystemId' => 'MPGU'));
//        $data = $sp->getAllpusInfo(array('779999','9992511111', '1987-02-06', 'MPGU'));
    }catch (\SoapFault $e){
        $e->getMessage();
    }

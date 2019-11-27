<?php

namespace Vidal\MainBundle\Appointment;

class AppSoap{

    /**
     * Данные для соединения
     */
    protected $certificate;

    protected $wsdl;

    protected $pass;

    protected $sslOptions;

    /**
     * указатель на соединение
     */
    protected $soap;

    /**
     * Данные для соединения
     */
    protected $data;

    function  __construct(){
        $this->certificate = '/var/www/vidal/web/sert/testSSLClient.pem';
        $this->wsdl = 'https://mosmedzdrav.ru:10002/emias-soap-service/PGUServicesInfo2?wsdl';
        $this->pass = 'testSSLClient';
        $this->user = array();
        $this->data['externalSystemId'] = 'MPGU';

        $this->sslOptions = array(
            'ssl' => array(
                'cafile' => "/var/www/vidal/web/sert/RootMedCA.cer",
                'allow_self_signed' => true,
                'verify_peer' => false,
            ),
        );

        $sslContext = stream_context_create($this->sslOptions);

        $this->soap = new \SoapClient($this->wsdl ,array(
            'local_cert' => $this->certificate,
            'passphrase'    => $this->pass,
            'stream_context' => $sslContext,
            'trace' => 0,
            'exceptions' => 0,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'wsdl_cache_enabled' => false
        ));
    }



    public function setData($name, $value){
        $this->data[$name] = $value;
        return $this;
    }

    public function getData($name){
        return $this->data[$name];
    }

    /**
     * Проверка массива Data на обязательные элементы
     */
    public function validationArray($names){
        foreach ($names as $name){
            if (!isset($this->data[$name])){
                return false;
            }
        }
        return true;
    }


    public function getSpecialities(){
        if ( $this->validationArray(array('omsNumber','birthDate','externalSystemId')) ){
            $data['omsNumber'] = $this->data['omsNumber'];
            $data['birthDate'] = $this->data['birthDate']->format('d-m-Y').'T00:00:00';
            $data['externalSystemId'] = $this->data['externalSystemId'];
            $result = $this->soap->getSpecialitiesInfo($data);
        }else{
            $result = array('Ошибка, не все данные заполнены');
        }
        return $result;
    }

    public function getDoctors(){
        if ( $this->validationArray(array('omsNumber','birthDate','specialityId','externalSystemId')) ){
            $data['omsNumber'] = $this->data['omsNumber'];
            $data['birthDate'] = $this->data['birthDate'];
            $data['externalSystemId'] = $this->data['externalSystemId'];
            $data['specialityId'] = $this->data['specialityId'];
            $result = $this->soap->getDoctorsInfo($data);
        }else{
            $result = array('Ошибка, не все данные заполнены');
        }
        return $result;
    }

    public function getAvailableResourceSchedule(){
        if ( $this->validationArray(array('omsNumber','birthDate','availableResourceId', 'complexResourceId','externalSystemId')) ){
            $data['omsNumber'] = $this->data['omsNumber'];
            $data['birthDate'] = $this->data['birthDate'];
            $data['externalSystemId'] = $this->data['externalSystemId'];
            $data['complexResourceId'] = $this->data['complexResourceId'];
            $data['availableResourceId'] = $this->data['availableResourceId'];
            $result = $this->soap->getDoctorsInfo($data);
        }else{
            $result = array('Ошибка, не все данные заполнены');
        }
        return $result;
    }

}
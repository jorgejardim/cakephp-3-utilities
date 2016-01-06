<?php
namespace Pagseguro\Controller\Component;
use Cake\Controller\Component;
use Cake\Routing\Router;

class PagseguroComponent extends Component
{
    const PAC = 1;
    const SEDEX = 2;
    const NOT_SPECIFIED = 3;

    public $request_pg;
    public $uid;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->uid = uniqid();
        $this->request_pg = new \PagSeguroPaymentRequest;
        $this->request_pg->setShippingType(self::NOT_SPECIFIED);
        $this->request_pg->setReference($this->uid);
        $this->request_pg->setCurrency('BRL');
    }

    public function item($descricao=null, $quantidade=null, $custo=null, $id=null)
    {
        $id = $id ? $id : uniqid();
        $custo = number_format($custo, 2, '.', '');
        $quantidade = $quantidade ? $quantidade : 1;
        $this->request_pg->addItem($id, $descricao, $quantidade, $custo);
    }

    public function referencia($referencia)
    {
        $this->request_pg->setReference($referencia);
    }

    public function cliente($nome, $email=null, $telefone=null, $nascimento=null, $documento=null, $tipo_documento='CPF')
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $area = substr($telefone, 0, 2);
        $telefone = substr($telefone, 2);
        $this->request_pg->setSender($nome, $email, $area, $telefone, $tipo_documento, $documento);
    }

    public function endereco($cep, $logradouro=null, $numero=null, $complemento=null, $bairro=null, $cidade=null, $estado='SP', $pais='BRA')
    {
        $cep = preg_replace('/\D/', '', $cep);
        $this->request_pg->setShippingAddress($cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $pais);
    }

    public function retorno($url)
    {
        $url = Router::url($url, true);
        $this->request_pg->setRedirectUrl($url);
    }

    public function notificacao($url)
    {
        $url = Router::url($url, true);
        $this->request_pg->addParameter('notificationURL', $url);
    }

    public function valorExtra($valor)
    {
        $this->request_pg->setExtraAmount($valor);
    }

    public function tipoFrete($tipo='SEDEX')
    {
        $shipping_code = \PagSeguroShippingType::getCodeByType($tipo);
        $this->request_pg->setShippingType($shipping_code);
    }

    public function addParametro($parametro, $valor)
    {
        $this->request_pg->addParameter($parametro, $valor);
    }

    public function enviar($test=false)
    {
        $email = Configure::read('Pagseguro.email');
        $token = Configure::read('Pagseguro.token');
        $environment = $test ? 'sandbox' : 'production';

        $config = new \PagSeguroAccountCredentials($email, $token);
        \PagSeguroConfig::setEnvironment($environment);
        return $this->request_pg->register($config);
    }

    public function verificarTransacao($codigo_notificacao)
    {
        $email = Configure::read('Pagseguro.email');
        $token = Configure::read('Pagseguro.token');

        $config = new \PagSeguroAccountCredentials($email, $token);
        return \PagSeguroNotificationService::checkTransaction(
            $config,
            $codigo_notificacao
        );
    }
}
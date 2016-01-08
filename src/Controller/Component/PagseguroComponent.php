<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\Core\Configure;

class PagseguroComponent extends Component
{
    const PAC = 1;
    const SEDEX = 2;
    const NOT_SPECIFIED = 3;

    public $request_pg;
    public $uid;
    public $sucesso = false;
    public $erro    = false;
    public $link;
    public $msg;
    public $codigo;
    public $referencia;

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->uid = uniqid();
        $this->request_pg = new \PagSeguroPaymentRequest;
        $this->request_pg->setShippingType(self::NOT_SPECIFIED);
        $this->request_pg->setReference($this->uid);
        $this->request_pg->setCurrency('BRL');
    }

    public function setItem($descricao=null, $quantidade=null, $custo=null, $id=null)
    {
        $id = $id ? $id : uniqid();
        $custo = $this->_formatMoeda($custo);
        $quantidade = $quantidade ? $quantidade : 1;
        $this->request_pg->addItem($id, $descricao, $quantidade, $custo);
    }

    public function setReferencia($referencia)
    {
        $this->identificador = $referencia;
        $this->request_pg->setReference($this->identificador);
    }

    public function setCliente($nome, $email=null, $telefone=null, $nascimento=null, $documento=null, $tipo_documento='CPF')
    {
        $telefone = preg_replace('/\D/', '', $telefone);
        $area = substr($telefone, 0, 2);
        $telefone = substr($telefone, 2);
        $this->request_pg->setSender($nome, $email, $area, $telefone, $tipo_documento, $documento);
    }

    public function setEndereco($cep, $logradouro=null, $numero=null, $complemento=null, $bairro=null, $cidade=null, $estado='SP', $pais='BRA')
    {
        $cep = preg_replace('/\D/', '', $cep);
        $this->request_pg->setShippingAddress($cep, $logradouro, $numero, $complemento, $bairro, $cidade, $estado, $pais);
    }

    public function setRetorno($url)
    {
        $url = Router::url($url, true);
        $this->request_pg->setRedirectUrl($url);
    }

    public function setNotificacao($url)
    {
        $url = Router::url($url, true);
        $this->request_pg->addParameter('notificationURL', $url);
    }

    public function setValorExtra($valor)
    {
        $this->request_pg->setExtraAmount($valor);
    }

    public function setTipoFrete($tipo='SEDEX')
    {
        $shipping_code = \PagSeguroShippingType::getCodeByType($tipo);
        $this->request_pg->setShippingType($shipping_code);
    }

    public function setParametro($parametro, $valor)
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
        $response = $this->request_pg->register($config);
        if($response) {
            $this->sucesso = true;
            $this->erro    = false;
            $this->link = $response;
            $this->msg = '';
            $exp = explode('=', $response);
            $this->codigo = $exp[1];
        } else {
            $this->sucesso = false;
            $this->erro    = true;
            $this->link = '';
            $this->msg = $response;
            $this->codigo = '';
        }
        return $response;
    }

    public function getTransacao($codigo_notificacao)
    {
        $email = Configure::read('Pagseguro.email');
        $token = Configure::read('Pagseguro.token');

        $config = new \PagSeguroAccountCredentials($email, $token);
        return \PagSeguroNotificationService::checkTransaction(
            $config,
            $codigo_notificacao
        );
    }

    private function _formatMoeda($valor)
    {
        $valor = preg_replace('/\D/', '', $valor);
        return substr($valor, 0, -2) . '.' . substr($valor, -2);
    }
}
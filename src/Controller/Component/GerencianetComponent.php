<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\Core\Configure;

class GerencianetComponent extends Component
{
    private $xml;
    private $assinatura         = false;
    public $pagamento_url       = 'https://go.gerencianet.com.br/api/pagamento/xml';
    public $pagamento_url_test  = 'https://go.gerencianet.com.br/teste/api/pagamento/xml';
    public $assinatura_url      = 'https://go.gerencianet.com.br/api/assinatura/xml';
    public $assinatura_url_test = 'https://go.gerencianet.com.br/teste/api/assinatura/xml';

    public function initialize(array $config)
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" ?><integracao />');
        $this->xml->addChild('itens')->addChild('item');
        $this->xml->addChild('cliente');
    }

    public function item($descricao=null, $quantidade=null, $custo=null, $id=null)
    {
        $this->xml->itens->item->addChild('itemValor', preg_replace('/\D/', '', $custo));
        $this->xml->itens->item->addChild('itemDescricao', $descricao);
        if ($quantidade) {
            $this->xml->itens->item->addChild('itemQuantidade', $quantidade);
        }
    }

    public function referencia($referencia)
    {
        $this->_retorno();
        $this->xml->retorno->addChild('identificador', $referencia);
    }

    public function cliente($nome, $email=null, $telefone=null, $nascimento=null, $documento=null, $tipo_documento='CPF')
    {
        $this->xml->cliente->addChild('nome', $nome);
        $this->xml->cliente->addChild('email', $email);
        $this->xml->cliente->addChild('celular', preg_replace('/\D/', '', $telefone));
        $this->xml->cliente->addChild('nascimento', $nascimento); // YYYY-MM-DD
        $this->xml->cliente->addChild('cpf', preg_replace('/\D/', '', $documento));
    }

    public function endereco($cep, $logradouro=null, $numero=null, $complemento=null, $bairro=null, $cidade=null, $estado='SP', $pais='BRA')
    {
        $this->xml->cliente->addChild('cep', preg_replace('/\D/', '', $cep));
        $this->xml->cliente->addChild('logradouro', $logradouro);
        $this->xml->cliente->addChild('numero', $numero);
        $this->xml->cliente->addChild('complemento', $complemento);
        $this->xml->cliente->addChild('bairro', $bairro);
        $this->xml->cliente->addChild('cidade', $cidade);
        $this->xml->cliente->addChild('estado', $estado);
    }

    public function retorno($url=null)
    {
        $this->_retorno();
        $url = Router::url($url, true);
        $this->xml->retorno->addChild('url', $url);
    }

    public function notificacao($url=null)
    {
        $this->_retorno();
        $url = Router::url($url, true);
        $this->xml->retorno->addChild('urlNotificacao', $url);
    }

    public function periodicidade($periodicidade)
    {
        $this->assinatura = true;
        $this->xml->addChild('periodicidade', $periodicidade);
    }

    public function ocorrencias($ocorrencias)
    {
        $this->xml->addChild('ocorrencias', $ocorrencias);
    }

    public function vencimento($vencimento)
    {
        $this->xml->addChild('vencimento', $vencimento); // YYYY-MM-DD
    }

    public function descricao($descricao)
    {
        $this->xml->addChild('descricao', $descricao);
    }

    public function desconto($desconto)
    {
        $this->xml->addChild('desconto', preg_replace('/\D/', '', $desconto));
    }

    public function frete($frete)
    {
        $this->xml->addChild('frete', preg_replace('/\D/', '', $frete));
    }

    public function marketplace($codigo)
    {
        $this->xml->itens->item->addChild('marketplace');
        $this->xml->itens->item->marketplace->addChild('codigo', $codigo);
    }

    public function enviar($test=false)
    {
        $token = Configure::read('Gerencianet.token');

        if ($this->assinatura) {
            $url  = $test ? $this->assinatura_url_test : $this->assinatura_url;
        } else {
            $url  = $test ? $this->pagamento_url_test : $this->pagamento_url;
        }
        $data = array('token' => $token, 'dados' => $this->xml->asXML());

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        return $this->response(curl_exec($ch));
    }

    public function xml()
    {
        return $this->xml->asXML();
    }

    private function response($response)
    {
        $response = json_decode(json_encode(simplexml_load_string($response)));
        return $response;
    }

    private function _retorno()
    {
        if(!isset($this->xml->retorno)) {
            $this->xml->addChild('retorno');
        }
    }
}
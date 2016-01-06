<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Routing\Router;
use Cake\Core\Configure;

class GerencianetComponent extends Component
{
    private $xml;
    public $sucesso = false;
    public $erro    = false;
    public $link;
    public $msg;
    public $codigo;
    public $referencia;
    public $pagamento_url       = 'https://go.gerencianet.com.br/api/pagamento/xml';
    public $pagamento_url_test  = 'https://go.gerencianet.com.br/teste/api/pagamento/xml';
    public $assinatura_url      = 'https://go.gerencianet.com.br/api/assinatura/xml';
    public $assinatura_url_test = 'https://go.gerencianet.com.br/teste/api/assinatura/xml';
    private $assinatura         = false;

    public function initialize(array $config)
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" ?><integracao />');
        $this->xml->addChild('itens')->addChild('item');
        $this->xml->addChild('cliente');
    }

    public function setItem($descricao=null, $quantidade=null, $custo=null, $id=null)
    {
        $this->xml->itens->item->addChild('itemValor', preg_replace('/\D/', '', $custo));
        $this->xml->itens->item->addChild('itemDescricao', $descricao);
        if ($quantidade) {
            $this->xml->itens->item->addChild('itemQuantidade', $quantidade);
        }
    }

    public function setReferencia($referencia)
    {
        $this->_retorno();
        $this->referencia = $referencia;
        $this->xml->retorno->addChild('identificador', $this->identificador);
    }

    public function setCliente($nome, $email=null, $telefone=null, $nascimento=null, $documento=null, $tipo_documento='CPF')
    {
        $this->xml->cliente->addChild('nome', $nome);
        $this->xml->cliente->addChild('email', $email);
        $this->xml->cliente->addChild('celular', preg_replace('/\D/', '', $telefone));
        $this->xml->cliente->addChild('nascimento', $nascimento); // YYYY-MM-DD
        $this->xml->cliente->addChild('cpf', preg_replace('/\D/', '', $documento));
    }

    public function setEndereco($cep, $logradouro=null, $numero=null, $complemento=null, $bairro=null, $cidade=null, $estado='SP', $pais='BRA')
    {
        $this->xml->cliente->addChild('cep', preg_replace('/\D/', '', $cep));
        $this->xml->cliente->addChild('logradouro', $logradouro);
        $this->xml->cliente->addChild('numero', $numero);
        $this->xml->cliente->addChild('complemento', $complemento);
        $this->xml->cliente->addChild('bairro', $bairro);
        $this->xml->cliente->addChild('cidade', $cidade);
        $this->xml->cliente->addChild('estado', $estado);
    }

    public function setRetorno($url=null)
    {
        $this->_retorno();
        $url = Router::url($url, true);
        $this->xml->retorno->addChild('url', $url);
    }

    public function setNotificacao($url=null)
    {
        $this->_retorno();
        $url = Router::url($url, true);
        $this->xml->retorno->addChild('urlNotificacao', $url);
    }

    public function setPeriodicidade($periodicidade)
    {
        $this->assinatura = true;
        $this->xml->addChild('periodicidade', $periodicidade);
    }

    public function setOcorrencias($ocorrencias)
    {
        $this->xml->addChild('ocorrencias', $ocorrencias);
    }

    public function setVencimento($vencimento)
    {
        $this->xml->addChild('vencimento', $vencimento); // YYYY-MM-DD
    }

    public function setDescricao($descricao)
    {
        $this->xml->addChild('descricao', $descricao);
    }

    public function setDesconto($desconto)
    {
        $this->xml->addChild('desconto', preg_replace('/\D/', '', $desconto));
    }

    public function setFrete($frete)
    {
        $this->xml->addChild('frete', preg_replace('/\D/', '', $frete));
    }

    public function setMarketplace($codigo)
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
        $response = $this->response(curl_exec($ch));
        if($response->status == 2) {
            $this->sucesso = true;
            $this->erro    = false;
            $this->link    = $response->resposta->link;
            $this->msg     = '';
            $this->codigo  = $response->resposta->transacao;
        } else {
            $this->sucesso = false;
            $this->erro    = true;
            $this->link    = '';
            $this->msg     = $response->erros->erros;
            $this->codigo  = $response->erros->codigo;
        }
        return $response;

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
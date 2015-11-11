#Utilities for CakePHP 3.x

- Gerencianet
- Cpanel XmlApi

## Install

Composer:
```
require: "jorge/cakephp3utilities": "dev-master"
```

## Configuration

```
'CPanel' => [
    'domain' => 'tryggu.com.br',
    'username' => 'tryggu',
    'password' => 't4r5zjj',
    'port' => '2082',
    'debug' => true,
],
'Gerencianet' => [
    'token' => 'ADFS7F834KDJULJORGE5993485H5KK3GG2234678',
],
```

## Gerencianet Example

```
$this->loadComponent('CakePHP3Utilities.Gerencianet');
$this->Gerencianet->item('Produto 1', 1, '1.200,00');
$this->Gerencianet->vencimento('2015-10-30');
$this->Gerencianet->retorno(time(), 'http://www.suaurl.com.br', 'http://www.suaurl.com.br');
$this->Gerencianet->cliente(
    'Maria da Silva',
    'email@teste.com.br',
    '(11) 98549-8123',
    '1980-11-24',
    '120.445.115-00'
);
$this->Gerencianet->endereco(
    '02462-020',
    'Rua Manoel Almeida Santos',
    '524',
    null,
    'V. Paulista',
    'Sao Paulo',
    'SP'
);
$this->Gerencianet->marketplace('3VTV93SFBKHL');
$this->Gerencianet->periodicidade(1);
$return = $this->Gerencianet->enviar();
```

## Cpanel XmlApi Example

```
$this->loadComponent('CakePHP3Utilities.CPanel');
if ($this->CPanel->domainCreatedSub('subdomain', 'yourdomain.com.br')) {
    # code...
} else {
    $this->Flash->error(__($this->CPanel->error));
}
```
#Utilities for CakePHP 3.x

## Instalação

Via composer:
```
require: "jorge/cakephp3utilities": "dev-master"
```
## Example Gerencianet

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

//marketplace
$this->Gerencianet->marketplace('3VTV93SFBKHL');

//assinatura
$this->Gerencianet->periodicidade(1);

//enviar
$return = $this->Gerencianet->enviar('ADFS76DF8345N34993485H5KK3GG2234678', true);
```
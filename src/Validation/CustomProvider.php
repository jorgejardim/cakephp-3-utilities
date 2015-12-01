<?php
/**
 * Validação para campos brasileiros
 *
 * MIT License
 *
 * @author     Jorge Jardim <jorge@jorgejardim.com.br>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 */
namespace CakePHP3Utilities\Validation;

use Exception;

class CustomProvider
{
    public function cnpj($cnpj) {

        if (strlen($cnpj) != 18)
            return false;

        $soma1 = ($cnpj[0] * 5) + ($cnpj[1] * 4) + ($cnpj[3] * 3) + ($cnpj[4] * 2) + ($cnpj[5] * 9) + ($cnpj[7] * 8) + ($cnpj[8] * 7) + ($cnpj[9] * 6) + ($cnpj[11] * 5) + ($cnpj[12] * 4) + ($cnpj[13] * 3) + ($cnpj[14] * 2);

        $resto = $soma1 % 11;

        $digito1 = $resto < 2 ? 0 : 11 - $resto;

        $soma2 = ($cnpj[0] * 6) + ($cnpj[1] * 5) + ($cnpj[3] * 4) + ($cnpj[4] * 3) + ($cnpj[5] * 2) + ($cnpj[7] * 9) + ($cnpj[8] * 8) + ($cnpj[9] * 7) + ($cnpj[11] * 6) + ($cnpj[12] * 5) + ($cnpj[13] * 4) + ($cnpj[14] * 3) + ($cnpj[16] * 2);

        $resto = $soma2 % 11;

        $digito2 = $resto < 2 ? 0 : 11 - $resto;

        return (($cnpj[16] == $digito1) && ($cnpj[17] == $digito2));
    }

    public function cpf($cpf) {

        $cpf = str_pad(ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);

        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' ||
            $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' ||
            $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
            return false;

        } else {

            for($t = 9; $t < 11; $t++) {
                for($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }

    public function cep($cep) {

        return true;
    }

    public function phone($phone) {

        return true;
    }

    public function cellphone($cellphone) {

        return true;
    }

    public function __call($method, $arguments)
    {
        if (!is_callable($method, $this)) {
            throw new Exception('Undefined respect validation method');
        }

        return call_user_func_array([$this, $method], $arguments);
    }
}
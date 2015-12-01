<?php
namespace CakePHP3Utilities\Utility;

class Text
{
    /**
     * Simple Password Generator
     *
     * Warning: This method it is a simple generator of unsafe passwords.
     *
     * @return strings
     */
    public static function PasswordGenerator($size=6, $force=0)
    {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';

        if ($force >= 1) {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($force >= 2) {
            $vowels .= "AEUY";
        }
        if ($force >= 4) {
            $consonants .= '23456789';
        }
        if ($force >= 8 ) {
            $vowels .= '@#$%';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $size; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }
}
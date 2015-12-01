<?php
namespace CakePHP3Utilities\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use Cake\Core\Configure;

class FacebookHelper extends Helper
{
    /**
     * Create a Facebook URL Login
     *
     * @return link
     */
    public function urlLogin($options = [])
    {
        return '<a id="' . $id . '" class="' . $class . '" href="' . Configure::read('fb_login_url') . '" title="' . $title . '" style="' . $style . '">'.$label.'</a>';
    }
}
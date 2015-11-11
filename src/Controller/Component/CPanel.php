<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;

require_once '../../Cpanel/xmlapi.php';

class CPanelComponent extends Component
{
    public  $xmlapi;
    public  $error;

    public function initialize(array $config)
    {
        $this->xmlapi = new \xmlapi(Configure::read('CPanel.domain'));
        $xmlapi->set_port(Configure::read('CPanel.port'));
        $xmlapi->password_auth(Configure::read('CPanel.username'), Configure::read('CPanel.password'));
        $xmlapi->set_output('json');
        $xmlapi->set_debug(Configure::read('CPanel.debug'));
    }

    public function tests()
    {
        return $this->xmlapi->listzones();
    }

    public function createdAccount($domain, $username, $password)
    {
        $acct = array( 'username' => $username, 'password' => $password, 'domain' => $domain);
        return $this->xmlapi->createacct($acct);
    }

    public function listAccount($domain, $username, $password)
    {
        $acct = array( 'username' => $username, 'password' => $password, 'domain' => $domain);
        return $this->xmlapi->createacct($acct);
    }

    public function domainCreatedSub($prefix, $domain, $path='public_html')
    {
        $subDomain = $prefix.'.'.$domain;
        $args = array($subDomain, $domain, 0, 0, $path);
        return $this->_resultBoolean($xmlapi->api1_query($username, 'SubDomain', 'addsubdomain', $args));
    }

    private function _resultBoolean($res)
    {
        $res  = json_decode($res);
        if (!isset($res->error)) {
            return true;
        } else {
            $this->error = $res->error;
            return false;
        }
    }
}
<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;

require_once dirname(dirname(dirname(__FILE__))) . '/Cpanel/xmlapi.php';

class CPanelComponent extends Component
{
    public  $xmlapi;
    public  $error;

    public function initialize(array $config)
    {
        $this->xmlapi = new \xmlapi(Configure::read('CPanel.domain'));
        $this->xmlapi->set_port(Configure::read('CPanel.port'));
        $this->xmlapi->password_auth(Configure::read('CPanel.username'), Configure::read('CPanel.password'));
        $this->xmlapi->set_output('json');
        $this->xmlapi->set_debug(Configure::read('CPanel.debug'));
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
        return $this->_resultBoolean($this->xmlapi->api1_query(Configure::read('CPanel.username'), 'SubDomain', 'addsubdomain', $args));
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
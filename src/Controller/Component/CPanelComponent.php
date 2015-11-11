<?php
namespace CakePHP3Utilities\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;

require_once dirname(dirname(dirname(__FILE__))) . '/Cpanel/xmlapi.php';

class CPanelComponent extends Component
{
    public  $xmlapi;
    public  $error;
    public  $return;
    public  $output;
    public  $config;

    public function initialize(array $config)
    {
        $this->connect();
    }

    public function connect($config = 'default')
    {
        $this->config = Configure::read('CPanel.'.$config);
        $this->xmlapi = new \xmlapi($this->config['domain']);
        $this->xmlapi->set_port($this->config['port']);
        $this->xmlapi->password_auth($this->config['username'], $this->config['password']);
        $this->xmlapi->set_output('json');
        $this->xmlapi->set_debug($this->config['debug']);
    }

    public function createdAccount($domain, $username, $password, $plan=null)
    {
        $acct = array('username' => $username, 'password' => $password, 'domain' => $domain, 'contactemail' => $this->config['contact']);
        if ($plan) {
            $acct['plan'] = $plan;
        }
        $this->output = $this->xmlapi->createacct($acct);
        $this->return = json_decode($this->output);
        if ($this->return->result[0]->status===1) {
            return true;
        }
        $this->error = $this->return->result[0]->statusmsg;
        return false;
    }

    public function domainCreatedSub($prefix, $domain, $path='public_html')
    {
        $subDomain = $prefix.'.'.$domain;
        $args = array($subDomain, $domain, 0, 0, $path);
        $this->output = $this->xmlapi->api1_query($this->config['username'], 'SubDomain', 'addsubdomain', $args);
        $this->return = json_decode($this->output);
        if (!isset($this->return->error)) {
            return true;
        }
        $this->error = $this->return->error;
        return false;
    }

    public function createdDB($database, $username, $password)
    {
        // create database
        $this->output = $this->xmlapi->api1_query($this->config['username'], 'Mysql', 'adddb', array($database));
        $this->return = json_decode($this->output);
        if (!isset($this->return->error)) {

            // create user
            $user = $this->xmlapi->api1_query($this->config['username'], 'Mysql', 'adduser', array($username, $password));

            // add database user
            $dbuser = $this->xmlapi->api1_query($this->config['username'], 'Mysql', 'adduserdb', array($database, $username, 'all'));

            $this->output = $user.$dbuser;
            $this->return = json_decode($dbuser);

            if (!isset($this->return->error)) {
                return true;
            }
        }
        $this->error = $this->return->error;
        return false;
    }
}
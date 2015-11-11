<?php
if(!$_GET['s']) die('add param "s"');

include_once('xmlapi.php');
$domain="tryggu.com.br";
$xmlapi = new xmlapi($domain);
$username = 'tryggu';
$password = 't4r5zjj';
$xmlapi->set_port(2082);
$xmlapi->password_auth($username,$password);
$xmlapi->set_debug(1);
$xmlapi->set_output('json');
$subDomainName = $_GET['s'].'.tryggu.com.br';
$args = array($subDomainName,$domain,0,0,'public_html');
$res = $xmlapi->api1_query($username,'SubDomain','addsubdomain', $args);
echo '<pre>';
print_r(json_decode(utf8_decode($res)));
echo '<hr>';
print_r($res);





















// include_once('xmlapi.php');
// $domain="tryggu.com.br";
// $xmlapi = new xmlapi($domain);
// $username = 'tryggu';
// $password = 't4r5zjj';
// $xmlapi->set_port(2082);
// $xmlapi->password_auth($username,$password);
// $xmlapi->set_debug(1);
// $xmlapi->set_output('xml');
// $subDomainName = 'your.tryggu.com.br';
// $args = array($subDomainName,$domain,0,0,'public_html/your');
// $res = $xmlapi->api1_query($username,'SubDomain','addsubdomain', $args);
// print_r($res);

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;
// $hostname ='localhost';
// $username ='sunilcha_crm';
// $password ='olCyMDiv(RPI';
// $database ='sunilcha_crm';	

$hostname ='localhost';
$username ='mauro_890d60e6dd43fc6dbf1303ff7a54f080';
$password ='6188229ff9434480dc0c389e20df57fa';
$database ='mauro_634a97942986e';	

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => $hostname,
	'username' => $username,
	'password' => $password,
	'database' => $database,
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

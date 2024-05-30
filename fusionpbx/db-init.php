<?php

$document_root = "/var/www/fusionpbx";
set_include_path($document_root);
$_SERVER["DOCUMENT_ROOT"] = $document_root;
$_SERVER["PROJECT_ROOT"] = $document_root;
define("PROJECT_PATH", '');

require_once "resources/functions.php";

require_once "resources/classes/text.php";
require_once "resources/classes/template.php";
require_once "resources/classes/message.php";
require_once "core/install/resources/classes/install.php";

session_start();

$language = new text;
$text = $language->get();

$debug = false;

$domain_uuid = uuid();


$_SESSION['install']['admin_username'] = "{admin_username}";
$_SESSION['install']['admin_password'] = "{admin_password}";
$_SESSION['install']['domain_name'] = "{domain_name}";
$_SESSION['install']['database_host'] = "{database_host}";
$_SESSION['install']['database_port'] = "{database_port}";
$_SESSION['install']['database_name'] = "{database_name}";
$_SESSION['install']['database_username'] = "{database_username}";
$_SESSION['install']['database_password'] = "{database_password}";


//build the config file
$install = new install;
$install->database_host = $_SESSION['install']['database_host'];
$install->database_port = $_SESSION['install']['database_port'];
$install->database_name = $_SESSION['install']['database_name'];
$install->database_username = $_SESSION['install']['database_username'];
$install->database_password = $_SESSION['install']['database_password'];
$result = $install->config();

$output = shell_exec('cd '.$_SERVER["DOCUMENT_ROOT"].' && php /var/www/fusionpbx/core/upgrade/upgrade_schema.php');
require_once dirname(__DIR__, 2) . "/resources/require.php";

//get the domain name
$domain_name = $_SESSION['install']['domain_name'];

//check to see if the domain name exists if it does update the domain_uuid
$sql = "select domain_uuid from v_domains ";
$sql .= "where domain_name = :domain_name ";
$parameters['domain_name'] = $domain_name;
$database = new database;
$domain_uuid = $database->select($sql, $parameters, 'column');
unset($parameters);

//set domain and user_uuid to true or false
if ($domain_uuid == null) {
  $domain_uuid = uuid();
  $domain_exists = false;
}
else {
  $domain_exists = true;
}

//if the domain name does not exist then add the domain name
if (!$domain_exists) {
  //add the domain permission
  $p = new permissions;
  $p->add("domain_add", "temp");

  //prepare the array
  $array['domains'][0]['domain_uuid'] = $domain_uuid;
  $array['domains'][0]['domain_name'] = $domain_name;
  $array['domains'][0]['domain_enabled'] = 'true';

  //save to the user data
  $database = new database;
  $database->app_name = 'domains';
  $database->app_uuid = 'b31e723a-bf70-670c-a49b-470d2a232f71';
  $database->uuid($domain_uuid);
  $database->save($array);
  $message = $database->message;
  unset($array);

  //remove the temporary permission
  $p->delete("domain_add", "temp");
}

//set the session domain id and name
$_SESSION['domain_uuid'] = $domain_uuid;
$_SESSION['domain_name'] = $domain_name;

//app defaults
$output = shell_exec('cd '.$_SERVER["DOCUMENT_ROOT"].' && php /var/www/fusionpbx/core/upgrade/upgrade_domains.php');

//prepare the user settings
$admin_username = $_SESSION['install']['admin_username'];
$admin_password = $_SESSION['install']['admin_password'];
$user_salt = uuid();
$password_hash = md5($user_salt . $admin_password);

//get the user_uuid if the user exists
$sql = "select user_uuid from v_users ";
$sql .= "where domain_uuid = :domain_uuid ";
$sql .= "and username = :username ";
$parameters['domain_uuid'] = $domain_uuid;
$parameters['username'] = $admin_username;

$database = new database;
$user_uuid = $database->select($sql, $parameters, 'column');
unset($parameters);

//if the user did not exist then get a new uuid
if ($user_uuid == null) {
  $domain_exists = false;
  $user_uuid = uuid();
}
else {
  $user_exists = true;
}

//set the user_uuid
$_SESSION['user_uuid'] = $user_uuid;

//get the superadmin group_uuid
$sql = "select group_uuid from v_groups ";
$sql .= "where group_name = :group_name ";
$parameters['group_name'] = 'superadmin';
$database = new database;
$group_uuid = $database->select($sql, $parameters, 'column');
unset($parameters);

//add the user permission
$p = new permissions;
$p->add("user_add", "temp");
$p->add("user_edit", "temp");
$p->add("user_group_add", "temp");

//save to the user data
$array['users'][0]['domain_uuid'] = $domain_uuid;
$array['users'][0]['user_uuid'] = $user_uuid;
$array['users'][0]['username'] = $admin_username;
$array['users'][0]['password'] = $password_hash;
$array['users'][0]['salt'] = $user_salt;
$array['users'][0]['user_enabled'] = 'true';
$array['user_groups'][0]['user_group_uuid'] = uuid();
$array['user_groups'][0]['domain_uuid'] = $domain_uuid;
$array['user_groups'][0]['group_name'] = 'superadmin';
$array['user_groups'][0]['group_uuid'] = $group_uuid;
$array['user_groups'][0]['user_uuid'] = $user_uuid;
$database = new database;
$database->app_name = 'users';
$database->app_uuid = '112124b3-95c2-5352-7e9d-d14c0b88f207';
$database->uuid($user_uuid);
$database->save($array);
$message = $database->message;
unset($array);

//remove the temporary permission
$p->delete("user_add", "temp");
$p->delete("user_edit", "temp");
$p->delete("user_group_add", "temp");

//update xml_cdr url, user and password in xml_cdr.conf.xml
if (!$domain_exists) {
  if (file_exists($_SERVER["DOCUMENT_ROOT"].PROJECT_PATH."/app/xml_cdr")) {
    xml_cdr_conf_xml();
  }
}

//write the switch.conf.xml file
if (!$domain_exists) {
  if (file_exists($switch_conf_dir)) {
    switch_conf_xml();
  }
}

#app defaults
$output = shell_exec('cd '.$_SERVER["DOCUMENT_ROOT"].' && php /var/www/fusionpbx/core/upgrade/upgrade_domains.php');

//set the max execution time to 1 hour
ini_set('max_execution_time',3600);

?>
<?php

/**
 * components/com_bbql/bbql.php
 * @license    GNU/GPL
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller

require_once( JPATH_COMPONENT . DS . 'controller.php' );

// Require specific controller if requested
if ($controller = JRequest::getWord('controller')) {
	$path = JPATH_COMPONENT . DS . 'controllers' . DS . $controller . '.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

// Create the controller
$classname = 'BbqlController' . $controller;
$controller = new $classname();

// add stylesheet
$document = & JFactory::getDocument();
$document->addStyleSheet(JRoute::_('components/com_bbql/css/bbql.css'));
$document->addStyleSheet(JRoute::_('components/com_bbql/css/tipTip.css'));
$document->addScript(JRoute::_('components/com_bbql/scripts/jquery-1.7.2.min.js'));
$document->addScript(JRoute::_('components/com_bbql/scripts/jquery.tipTip.minified.js'));

global $systemPathToComponent, $httpPathToComponent, $userList, $joomlaUser, $joomlaDb, $bbqlDb;
$systemPathToComponent = dirname(__FILE__);
$httpPathToComponent = '.' . DS . 'components' . DS . 'com_bbql';
$joomlaUser = & JFactory::getUser();
$joomlaDb = & JFactory::getDBO();

// set path of database file
$db = 'sqlite:' . $systemPathToComponent . DS . 'resources' . DS . 'bbql.db';
//$db = 'sqlite:c:\www\bbd2\components\com_bbql\resources\bbql.db';
// create a SQLite3 database file with PDO and return a database handle (Object Oriented)
try {
	$bbqlDb = new PDO($db);
} catch (PDOException $exception) {
	die($exception->getMessage());
}

$auth = & JFactory::getACL();

$auth->addACL('com_bbql', 'createLeague', 'users', 'super administrator');
$auth->addACL('com_bbql', 'createLeague', 'users', 'administrator');
$auth->addACL('com_bbql', 'createLeague', 'users', 'manager');
$auth->addACL('com_bbql', 'createLeague', 'users', 'publisher');
$auth->addACL('com_bbql', 'createLeague', 'users', 'editor');
$auth->addACL('com_bbql', 'createLeague', 'users', 'author');

$auth->addACL('com_bbql', 'joinLeague', 'users', 'super administrator');
$auth->addACL('com_bbql', 'joinLeague', 'users', 'administrator');
$auth->addACL('com_bbql', 'joinLeague', 'users', 'manager');
$auth->addACL('com_bbql', 'joinLeague', 'users', 'publisher');
$auth->addACL('com_bbql', 'joinLeague', 'users', 'editor');
$auth->addACL('com_bbql', 'joinLeague', 'users', 'author');
$auth->addACL('com_bbql', 'joinLeague', 'users', 'registered');

$auth->addACL('com_bbql', 'admin', 'users', 'super administrator');
$auth->addACL('com_bbql', 'admin', 'users', 'administrator');
$auth->addACL('com_bbql', 'admin', 'users', 'manager');
$auth->addACL('com_bbql', 'admin', 'users', 'publisher');


$user = & JFactory::getUser();
if ($user->authorize('com_bbql', 'admin')) {
	ini_set("display_errors", 1);
}

//populate the userList
$sql = 'SELECT id, username FROM #__users ORDER BY username';
$joomlaDb->setQuery($sql);
$result = $joomlaDb->loadObjectList();

$userList = array();
//set up an array where the index is the user Id and value is the username
for ($i = 0; $i < count($result); $i++) {
	$userList[$result[$i]->id] = $result[$i]->username;
}

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

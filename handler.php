<?php
//ini_set('display_errors', 1);
include '/home/soemail/html/api/SoMail.php';

$db;
mysqlSetup($db);

//echo $_SERVER['REQUEST_URI'];

// find the function/method to call
$callback = NULL;
if (preg_match('/api\/([^\/\?]+)/', $_SERVER['REQUEST_URI'], $m)) {
  $callback = $m[1];
} 


//echo "\n$callback\n";
switch ($callback) {
	case 'findPath':
//		echo "findPath";
		if(!isset($_REQUEST['uid'],$_REQUEST['from'],$_REQUEST['to']))
			echo -2;
		else
			echo findPath($_REQUEST['uid'],$_REQUEST['from'],$_REQUEST['to']);
		break;
	case 'verifyXdsl':
		if(!isset($_REQUEST['xdsl'], $_REQUEST['to'], $_REQUEST['uid']))
			echo -2;
		else
		{
			$xdslPath=verifyXdsl($_REQUEST['xdsl'], trim($_REQUEST['to'],","), $_REQUEST['uid'], $db);
			if($xdslPath != "Can't verify X-DSL.")
				echo "<div id=\"xdslTrust\" style=\"display: none;\">". $_REQUEST['xdsl'] . "," . 
					origXdslTrust($_REQUEST['uid'], $_REQUEST['xdsl'], $db) . "," . 
					xdslTrust($_REQUEST['uid'], $_REQUEST['xdsl'], $db) .
					"</div>";
			echo $xdslPath;
		}
		break;
	case 'registerXdsl':
		if(!isset($_REQUEST['uid'], $_REQUEST['paths']))
				echo -2;
		else
				echo registerXdsl($_REQUEST['uid'], $_REQUEST['paths'], $db);
		break;
	case 'xdslTrust':
		if(!isset($_REQUEST['xdsl'], $_REQUEST['uid']))
			echo -2;
		else
			echo origXdslTrust($_REQUEST['uid'], $_REQUEST['xdsl'], $db) .",".xdslTrust($_REQUEST['uid'], $_REQUEST['xdsl'], $db);
		break;
}
?>

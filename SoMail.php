<?
define('SM_PATH','/home/soemail/html/');
//require_once(SM_PATH . 'functions/dsl_func.php');
//require_once('dsl_func.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . "facebook/facebook.php");




function injectJS()
{

	echo "<script type='text/javascript'>\n" .
		"function findPaths() {\n" .
		"  var url=\"findpaths.php?uid=" . getUserInfo($username) . "\";\n" . 
		"  if(document.compose.send_to.value != \"\"){\n" .
		"   url+= \"&send_to=\" + document.compose.send_to.value;\n" . 
		"  }\n" . 
		"  if(document.compose.send_to_cc.value != \"\"){\n" . 
		"    url+= \"&send_to_cc=\" + document.compose.send_to_cc.value;\n" . 
		"  }\n" .
		"  if(document.compose.send_to_bcc.value != \"\"){\n" . 
		"    url+= \"&send_to_bcc=\" + document.compose.send_to_bcc.value;\n" .
		"  }\n" .
		"  if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari\n" . 
		"    xmlhttp=new XMLHttpRequest();\n" . 
		"  } else {// code for IE6, IE5\n" .
		"    xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");\n" . 
		"  }\n" . 
		"  xmlhttp.open(\"GET\",url,false);\n" .
		"  xmlhttp.send(null);\n" . 
		"  document.getElementById('paths').innerHTML=xmlhttp.responseText;\n" .
		"}\n" . 
		"</script>\n";
}

function readMail()
{
	global $username; //DSL
	mysqlSetup($db); //DSL
   //DSL - Added a script to set outcome.
    $s  = "<script type='text/javascript'>\n" .
          "function setOutcome(messageID, outcome) {\n" .
          "  var url=\"http://cyrus.cs.ucdavis.edu/~dslfaith/php/soemail_setoutcome.php?uid=" .
             getUserIdFromEmail($username) . "&msg_id=\" + messageID + \"&outcome=\" + outcome;" . 
          "  if (window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari\n" .
          "    xmlhttp=new XMLHttpRequest();\n" .
          "  } else {// code for IE6, IE5\n" .
          "    xmlhttp=new ActiveXObject(\"Microsoft.XMLHTTP\");\n" .
          "  }\n" .
          "  xmlhttp.open(\"GET\",url,false);\n" .
          "  xmlhttp.send(null);\n" .
          "  document.getElementById('paths').innerHTML=xmlhttp.responseText;\n" .
          "}\n" .
          "</script>\n";

	//DSL 01/30/10. Adding social path in the header
	$msgID = $header->x_dsl;
	if($msgID != -1){
		$path  = '<tr>';
		$path .= html_tag('td', '<b>Social Path:&nbsp;&nbsp;</b>', 'right', '', 'valign="top" width="20%"') . "\n";
		$query = "SELECT path FROM messages WHERE msgID=$msgID AND recipientID=" .
			getUserIdFromEmail($username);
		$result = mysql_fetch_row(mysql_query($query, $db));
		$route = $result[0];
		$nodes = explode(",",$result[0]);

		$pathDisplay = "<table><tr>";
		$firstNode = true;
		foreach($nodes as $node){
			$name = uidToName($node);
			if(!$firstNode){
				$pathDisplay .= "<td>&nbsp; &#8658; &nbsp;</td>";
			}
			$pathDisplay .= "<td valign='top'><center><a " . 
				"href='http://www.facebook.com/profile.php?id=" . $node . "'><img src='" .
				getPic($node,$uid) . "'><br>";
			$pathDisplay .= "$name</a></center></td>";
			$firstNode=false;
		}
		$pathDisplay .= "</tr></table>";
		$path .= html_tag('td', $pathDisplay, 'left', '', 'valign="top" width="80%"') . "\n";
		$path .= '</tr>';

		$path .= '<tr>';
		$path .= html_tag('td', '<b>Spam:&nbsp;&nbsp;</b>', 'right', '', 'valign="top" width="20%"') . "\n";

		$spamQuestion = "Is this message spam? " . 
			"<input type='radio' name='outcome' onClick='setOutcome($msgID,0);'>Yes " . 
			"<input type='radio' name='outcome' onClick='setOutcome($msgID,1);'>No" .
			" (Will update automatically when you make your selection.)";
		$path .= html_tag('td', $spamQuestion, 'left', '', 'valign="top" width="80%"') . "\n";
		$path .= '</tr>';

		echo $path;
	}
	// END DSL STUFF **********************************************************
}

function verifyXdsl($xdsl,$username, $uid, $db)
{
	$msgID = $xdsl;
	$username = trim($username,", ");
	if($uid != getUserIdFromEmail($username))
			return "Can't verify X-DSL.";
	if($msgID != -1){
		$path  = '<table><tr>';
		$path .= '<td align="right" valign="top" width="20%"><b>Social Path:&nbsp;&nbsp;</b></td>';
		$query = "SELECT path FROM messages WHERE msgID=$msgID AND recipientID=" .
			getUserIdFromEmail($username);
		$result = mysql_fetch_row(mysql_query($query, $db));
		if(mysql_affected_rows() != 1)
			return "Can't verify X-DSL.";
		$route = $result[0];
		$nodes = explode(",",$result[0]);

		$pathDisplay = "<table><tr>";
		$firstNode = true;
		foreach($nodes as $node){
			$name = uidToName($node,$uid);
			if(!$firstNode){
				$pathDisplay .= "<td>&nbsp; &#8658; &nbsp;</td>";
			}
			$pathDisplay .= "<td valign='top'><center><a " . 
				"href='http://www.facebook.com/profile.php?id=" . $node . "'><img src='" .
				getPic($node,$uid) . "'/><br/>";
			$pathDisplay .= "$name</a></center></td>";
			$firstNode=false;
		}
		$pathDisplay .= "</tr></table></td>";
		$path .= '<td align="left" valign="top" width="80%">' . $pathDisplay;
		$path .= '</tr>';

		$path .= '<tr>';
		$path .= '<td align="right" valign="top" width="20%"><b>Spam:&nbsp;&nbsp;</b></td>';

		$spamQuestion = "Is this message spam? " . 
			"<input type='radio' name='outcome' onClick='setOutcome($msgID,0);'/>Yes " . 
			"<input type='radio' name='outcome' onClick='setOutcome($msgID,1);'/>No" .
			" (Will update automatically when you make your selection.)";
		$path .= '<td align="left" valign="top" width="80%">'. $spamQuestion . '</td>';
		$path .= '</tr></table>';

		return $path;
	}	
}
/*
function sendMail()
{

	$dsl_has_msg = false;

	//DSL 01/25/10 ************************************************************
	//Update email list, to determine who the email is sucessfully sent to...
	global $socialPaths;
	mysqlSetup($db);
	//print_r($socialPaths);

	$failed_recips = array();
	$no_path_recips = array();
	$asocial_recips = array();

	$send_to_arr = explode(",",(str_replace(" ","",$send_to)));
	$new_send_to = "";
	foreach($send_to_arr as $recip){
		//User did not specify a path for this recipient and they exist in system. 
		//We will attempt to find a path.
		if(!isset($socialPaths[str_replace(".","_",$recip)]) && getUserIdFromEmail($recip)!=""){
			$path = findSocialPath(getUserIdFromEmail($username),getUserIdFromEmail($recip));
			if(is_array($path)){ //a path was found.
				$nodes = "";
				foreach($path as $node){
					$nodes = $nodes . $node[0] . ",";
				}
				$nodes = substr($nodes,0,-1);
				$socialPaths[str_replace(".","_",$recip)] = $nodes;
			} else { //Recipient exists in system, but no path was found.
				$no_path_recips[] = $recip;
				continue; //i.e. do not send this message out to this recipient.
			}
		}

		if(isset($socialPaths[str_replace(".","_",$recip)])) { //Path exists
			$success = sendSocialMessage(explode(",",$socialPaths[str_replace(".","_",$recip)]));
			if($success){
				//echo "$success!";
				$new_send_to = $new_send_to . $recip . ",";
			} else {
				//echo "fail";
				$failed_recips[] = $recip;
			}
		} else { //Recipient does not exist in system. Send mail asocially.
			$asocial_recips[] = $recip;
			$new_send_to = $new_send_to . $recip . ",";
		}
	}
	$new_send_to = substr($new_send_to,0,-1);
	$send_to = $new_send_to;
	if($send_to==""){
		$msg = "<b>Your email was dropped on it's way to each of your recipients in the 'To' field due to DSL:</b><br><ul>";
		foreach($send_to_arr as $recip){
			$msg .= "<li>$recip";
		}
		$msg .= "</ul>";
		$msg .= "As a result, your email was not delivered.<br>";
		plain_error_message($msg,$color);
	}

	if(!empty($send_to_cc)){
		$send_to_cc_arr = explode(",",(str_replace(" ","",$send_to_cc)));
		$new_send_to_cc = "";
		foreach($send_to_cc_arr as $recip){
			//User did not specify a path for this recipient and they exist in system.
			//We will attempt to find a path.
			if(!isset($socialPaths[str_replace(".","_",$recip)]) && getUserIdFromEmail($recip)!=""){
				$path = findSocialPath(getUserIdFromEmail($username),getUserIdFromEmail($recip));
				if(is_array($path)){ //a path was found.
					$nodes = "";
					foreach($path as $node){
						$nodes = $nodes . $node[0] . ",";
					}
					$nodes = substr($nodes,0,-1);
					$socialPaths[str_replace(".","_",$recip)] = $nodes;
				} else { //Recipient exists in system, but no path was found.
					$no_path_recips[] = $recip;
					continue; //i.e. do not send this message out to this recipient.
				}
			}

			if(isset($socialPaths[str_replace(".","_",$recip)])) { //Path exists
				if(sendSocialMessage(explode(",",$socialPaths[str_replace(".","_",$recip)]))){
					$new_send_to_cc = $new_send_to_cc . $recip . ",";
				} else {
					$failed_recips[] = $recip;
				}
			} else { //Recipient does not exist in system. Send mail asocially.
				$asocial_recips[] = $recip;
				$new_send_to_cc = $new_send_to_cc . $recip . ",";
			}
		}
		$new_send_to_cc = substr($new_send_to_cc,0,-1);
		$send_to_cc = $new_send_to_cc;
	}//if(!empty($send_to_cc){ 

	if(!empty($send_to_bcc)){
		$send_to_bcc_arr = explode(",",(str_replace(" ","",$send_to_bcc)));
		$new_send_to_bcc = "";
		foreach($send_to_bcc_arr as $recip){
			//User did not specify a path for this recipient and they exist in system.
			//We will attempt to find a path.
			if(!isset($socialPaths[str_replace(".","_",$recip)]) && getUserIdFromEmail($recip)!=""){
				$path = findSocialPath(getUserIdFromEmail($username),getUserIdFromEmail($recip));
				if(is_array($path)){ //a path was found.
					$nodes = "";
					foreach($path as $node){
						$nodes = $nodes . $node[0] . ",";
					}
					$nodes = substr($nodes,0,-1);
					$socialPaths[str_replace(".","_",$recip)] = $nodes;
				} else { //Recipient exists in system, but no path was found.
					$no_path_recips[] = $recip;
					continue; //i.e. do not send this message out to this recipient.
				}
			}

			if(isset($socialPaths[str_replace(".","_",$recip)])) { //Path exists
				if(sendSocialMessage(explode(",",$socialPaths[str_replace(".","_",$recip)]))){
					//echo "1";
					$new_send_to_bcc = $new_send_to_bcc . $recip . ",";
				} else {
					//echo "0";
					$failed_recips[] = $recip;
				}
			} else { //Recipient does not exist in system. Send mail asocially.
				$asocial_recips[] = $recip;
				$new_send_to_bcc = $new_send_to_bcc . $recip . ",";
			}
		}
		$new_send_to_bcc = substr($new_send_to_bcc,0,-1);
		$send_to_bcc = $new_send_to_bcc;
	}//if(!empty($send_to_bcc)){

	if(!empty($no_path_recips) || !empty($failed_recips) || 
			!empty($asocial_recips)){//Not everything went smoothly. User should be
		//notified.
		//print_r($failed_recip);
		//print_r($asocial_recips);
		$dsl_has_msg = true;
		$msg = "";
		if(!empty($failed_recips)){
			$msg .= "<b>DSL has dropped the message to the following users because of trust:</b><BR><ul>";
			foreach($failed_recips as $recip){
				$msg .= "<li>$recip";
			}
			$msg .= "</ul>";
		}

		if(!empty($no_path_recips)){
			$msg .= "<b>DSL could not find social paths to the following users (Message not deliverd to them):</b><BR><ul>";
			foreach($no_path_recips as $recip){
				$msg .= "<li>$recip";
			}
			$msg .= "</ul>";
		}

		if(!empty($asocial_recips)){
			$msg .= "<b>The following users do not exist in DSL and the message will be sent to them without any social context:</b><BR><ul>";
			foreach($asocial_recips as $recip){
				$msg .= "<li>$recip";
			}
			$msg .= "</ul>";
		}

		plain_error_message($msg,$color);
	}
	//End DSL stuff ***********************************************************

	// DSL: Add social context to the header. X-DSL
	// Update the database with this info.
	$all_recipients = array_merge(explode(",",$send_to),explode(",",$send_to_cc));
	$all_recipients = array_merge($all_recipients,explode(",",$send_to_bcc));
	$firstTime=true;
	$x_dsl = "NULL";
	foreach($all_recipients as $recip){
		if(isset($socialPaths[str_replace(".","_",$recip)])) {
			$query = "INSERT INTO messages VALUES($x_dsl," .
				getUserIdFromEmail($username) . "," .
				getUserIdFromEmail($recip) . ",\"" .
				$socialPaths[str_replace(".","_",$recip)] . "\",NULL)";
			mysql_query($query,$db);
			if($firstTime){
				$x_dsl = mysql_insert_id();
				$firstTime= false;
			}
		}
	}

	if($firstTime){
		$rfc822_header->x_dsl = -1;
	} else {
		$rfc822_header->x_dsl = $x_dsl;
	}
	// END DSL ****************************************************************



}
*/
function origXdslTrust($uid, $xdsl, $db)
{
/*
 * mysql> select * from messages_trust;
 * +-------+------------+-------------+-----------------+
 * | msgID | senderID   | recipientID | trust           |
 * +-------+------------+-------------+-----------------+
 * |   385 | 1271758422 |  1206111571 | 1,0.5000,0.2778 | 
 * +-------+------------+-------------+-----------------+
 * 1 row in set (0.00 sec)
 *
 */
	$query = "SELECT trust FROM messages_trust WHERE msgID=".
		mysql_real_escape_string($xdsl) . " AND recipientID=" .
		mysql_real_escape_string($uid);
	//echo $query;
	$result = mysql_fetch_row(mysql_query($query, $db));
	if(mysql_affected_rows() != 1){
	//No original path exists..
	
		return "-1,-1";

	}
	$nodes = explode(",",$result[0]);

	//echo "<pre>";
	//print_r($path);
	//print_r($nodes);
	//echo "<BR>";
	
	$trust=1;
	foreach($nodes as $node)
		$trust*=$node;
	$return = $trust . "," . (count($nodes)-1);
	return $return;
}
function insertXdslTrust($db)
{
	$query = "SELECT msgID, senderID, recipientID, path FROM messages;";
	$result = mysql_query($query, $db);
	while ($row = mysql_fetch_array($result)) {
		$nodes = explode(",",$row["path"]);

		$path = findSocialPath($row["senderID"],$row["recipientID"],$row["recipientID"]);
		echo "<pre>";
		//print_r($path);
		//print_r($nodes);
		//echo "<BR>";
		if($path == -1)
			continue; //return "-1,-1";
		//Verify that we have the same path as the x-dsl..
		if(count($path) != count($nodes))
			continue;
		
		foreach($nodes as $i => $node)
			if($nodes[$i] != $path[$i][0])
				continue; //return "-2,-2";
		$trust = "";

		foreach($path as $vp){
			$trust .= $vp[1] . ",";
		}

		$query = "INSERT INTO messages_trust (msgID,senderID,recipientID,trust) VALUES (" .
			$row["msgID"] ." , " . $row["senderID"] .",". $row["recipientID"] .", \"" . trim($trust,",") . "\")";
		//echo $query . "\n";
				mysql_query($query,$db);

	}
}
function xdslTrust($uid, $xdsl, $db)
{
	$query = "SELECT path,senderID FROM messages WHERE msgID=".
		mysql_real_escape_string($xdsl) . " AND recipientID=" .
		mysql_real_escape_string($uid);
	$result = mysql_fetch_row(mysql_query($query, $db));
	if(mysql_affected_rows() != 1)
		return "-1";
	$nodes = explode(",",$result[0]);

	$path = findSocialPath($result[1],$uid,$uid);
	//echo "<pre>";
	//print_r($path);
	//print_r($nodes);
	//echo "<BR>";
	if($path == -1)
		return "-1,-1";
//Verify that we have the same path as the x-dsl..
	foreach($nodes as $i => $node)
		if($nodes[$i] != $path[$i][0])
			$unvalidpath = true;
	//return "-2,". (count($path)-1);
	
	
	$trust=1;
	foreach($path as $node)
		$trust*=$node[1];
	$return = $trust . "," . (count($path)-1);
	return $return;
}

function findPath($uid, $from, $to)
{
	$from = trim($from,",");
	$to = trim($to,",");
//Verify the from mail and $uid.
	if($uid != getUserIdFromEmail($from))
		return -1;

	$return = "";

	$to_emails = explode(",", $to);

	foreach($to_emails as $to_email){
		$userID_to = getUserIdFromEmail($to_email);
		if($userID_to>0){
			$return .= "$to_email:<br><table>";
			$path = findSocialPath($uid,$userID_to,$from);
			//print_r($path);
			//echo "<BR>";

			$nodes = "";
			$pathDisplay = "";

			$firstNode = true;
			foreach($path as $node){
				$name = uidToName($node[0],$from);
				if(!$firstNode){
					$pathDisplay .= "<td>&nbsp; &#8658; &nbsp;</td>";
				}
				$pathDisplay .= "<td valign='top'><center><a href='http://www.facebook.com/profile.php?id=" . $node[0] . "'><img src='" . getPic($node[0],$uid) . "'/><br/>";
				$pathDisplay .= "$name</a>";
				if(!$firstNode){
					$pathDisplay .= "<br/>Probability: " . $node[1];
				}
				$pathDisplay .= "</center></td>";
				//$pathDisplay = $pathDisplay . $name . "->";
				$nodes = $nodes . $node[0] . ",";
				$firstNode=false;
			}
			$nodes = substr($nodes,0,-1);
			$return .= "<tr><td><input type='radio' name='" . str_replace(".","_",$to_email) . "' value='$nodes' checked=\"checked\" class=\"inputXdsl\"/></td>$pathDisplay</tr></table>";
		}
	}
	return $return;
}
function registerXdsl($uid, $p, $db)
{
	$x_dsl = "NULL";
	$paths = explode(":",trim($p,":"));

	foreach( $paths as $path )
	{
		$verifyPath = $hops = explode(",",$path);
		
		//Verify that the sender is the firstone in the path.
		if($uid != array_shift($hops))
			return "-2";
		
		//Verify that the given path is really valid.
		//    $sender = $uid;
		$recipient = array_pop($hops);
		$findSP = findSocialPath($uid, $recipient);
		$trust = "";
		foreach($verifyPath as $key => $vp){
		//Verify that the given path is really valid.
			if($vp != $findSP[$key][0])
				return -1;
			$trust .= $findSP[$key][1] . ",";
		}
		//RETURN -1
		//FAITH please..
		//RETURN -1
		/*
		 * Should create entities in DB like this:
		 * mysql> select * from messages where msgID=317;
		 * +-------+------------+-------------+---------------------------------+---------+
		 * | msgID | senderID   | recipientID | path                            | outcome |
		 * +-------+------------+-------------+---------------------------------+---------+
		 * |   317 | 1271758422 |  1206111571 | 1271758422,581205756,1206111571 |    NULL | 
		 * |   317 | 1271758422 |  1214439232 | 1271758422,581205756,1214439232 |    NULL | 
		 * +-------+------------+-------------+---------------------------------+---------+
		 * 2 rows in set (0.00 sec)
		 *
		 */
		// DSL: Add social context to the header. X-DSL
		// Update the database with this info.
		$query = "INSERT INTO messages VALUES($x_dsl ," .
			mysql_real_escape_string($uid) . "," . 
			mysql_real_escape_string($recipient) .  ",\"" .  
			mysql_real_escape_string($path) . "\",NULL)";
		mysql_query($query,$db);
		if($x_dsl == "NULL")
			$x_dsl = mysql_insert_id();
/*
 * mysql> describe messages_trust;
 * +-------------+--------------+------+-----+---------+-------+
 * | Field       | Type         | Null | Key | Default | Extra |
 * +-------------+--------------+------+-----+---------+-------+
 * | msgID       | int(11)      | YES  |     | NULL    |       | 
 * | senderID    | bigint(20)   | YES  |     | NULL    |       | 
 * | recipientID | bigint(20)   | YES  |     | NULL    |       | 
 * | trust       | varchar(256) | YES  |     | NULL    |       | 
 * +-------------+--------------+------+-----+---------+-------+
 */
		$query = "INSERT INTO messages_trust (msgID,senderID,recipientID,trust) VALUES ($x_dsl, ".
			mysql_real_escape_string($uid) .  "," .  
			mysql_real_escape_string($recipient) .  ",\"" .  
			mysql_real_escape_string(trim($trust,",")) . "\")";
		mysql_query($query,$db);
	}
	return $x_dsl;
}

?>

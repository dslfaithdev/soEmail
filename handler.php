<?php

echo $_SERVER['REQUEST_URI'];

// find the function/method to call
$callback = NULL;
if (preg_match('/api\/([^\/\?]+)/', $_SERVER['REQUEST_URI'], $m)) {
  $callback = $m[1];
} 


echo "\n$callback\n";
switch ($callback) {
  case 'findPath':
    echo "findPath";
    break;
  case 'verifyXdsl':
    echo "verifyXdsl";
    echo $_REQUEST['uid'];
    break;
  case 'registerXdsl':
    echo "registerXdsl";
    break;
  case 'xdslTrust':
    break;

}
?>

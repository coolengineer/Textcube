<?
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'title' => array('string', 'default' => '')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (setBlogTitle($owner, trim($_GET['title']))) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
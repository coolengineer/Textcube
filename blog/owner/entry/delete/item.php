<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (isset($_GET['no_javascript'])) {
	deleteEntry($owner, $suri['id']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
} else {
	if (deleteEntry($owner, $suri['id']) === true) {
		respondResultPage(0);
	} else
		respondResultPage( - 1);
}
?>
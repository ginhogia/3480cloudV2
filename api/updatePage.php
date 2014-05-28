<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
$api = new Api($_POST);
$api->checkParameter("id");
$api->checkParameter("owner");

$page_id = $api->param("id");
$owner = $api->param("owner");

if (count($owner) == 0)
	$api->returnCustomError(1, "There must be at least one owner");

$page = Page::getPageById($page_id, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

if (!$page->setOwner($owner))
	$api->returnCustomError(2, "Unable to set owner");
$api->returnSuccess();
?>
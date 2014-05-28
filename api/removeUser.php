<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
$api = new Api($_POST);
$api->checkParameter("id");

$user_id = $api->param("id");

$page = Page::getPageById(PAGE_ID_MEMBER, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$user = User::getUserById($user_id);
if (is_null($user))
	$api->returnCustomError(1, "User id does not exist.");
if (!$user->remove())
	$api->returnCustomError(2, "Unable to remove user.");
$api->returnSuccess();
?>
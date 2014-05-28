<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/event.php");
$api = new Api($_POST);
$api->checkParameter("id");

$page = Page::getPageById(PAGE_ID_TIMELINE, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$id = $api->param("id");
$resource = EventResource::getResourceById($id);
if (!$resource)
{
	$api->returnCustomError(1, "Resource not found.");
}

if ($resource->isExist() && !unlink($resource->getPath()))
{
	$api->returnCustomError(2, "Cannot unlink file.");
}

if (!$resource->remove())
{
	$api->returnCustomError(3, "Cannot remove DB record.");
}

$api->returnSuccess();
?>
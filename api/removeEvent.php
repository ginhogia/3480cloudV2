<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/event.php");
$api = new Api($_POST);
$api->checkParameter("id");

$event_id = $api->param("id");

$page = Page::getPageById(PAGE_ID_TIMELINE, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$event = Event::getEvent($event_id);
if (is_null($event))
	$api->returnCustomError(1, "Event id does not exist.");
if (!$event->remove())
	$api->returnCustomError(2, "Unable to remove event.");
$api->returnSuccess();
?>
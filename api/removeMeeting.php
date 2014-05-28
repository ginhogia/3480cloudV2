<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/meeting.php");
$api = new Api($_POST);
$api->checkParameter("id");

$meeting_id = $api->param("id");

$page = Page::getPageById(PAGE_ID_SCHEDULE, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$meeting = Meeting::getMeeting($api->getSession()->getClub()->getId(), $meeting_id);
if (is_null($meeting))
	$api->returnCustomError(1, "Meeting id does not exist.");
if (!$meeting->remove())
	$api->returnCustomError(2, "Unable to remove meeting.");
$api->returnSuccess();
?>
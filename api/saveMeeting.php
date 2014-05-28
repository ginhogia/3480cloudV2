<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/meeting.php");
$api = new Api($_POST);
$api->checkParameter("original_id");
$api->checkParameter("id");
$api->checkParameter("date");
$api->checkParameter("topic");
$api->checkParameter("type");

$meeting_id = $api->param("original_id");

$page = Page::getPageById(PAGE_ID_SCHEDULE, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$meeting = Meeting::getMeeting($api->getSession()->getClub()->getId(), $meeting_id);
if (!is_null($meeting))
	$meeting->remove();

$data = array();
$data["meeting_id"] = $api->param("id");
$data["club_id"] = $api->getSession()->getClub()->getId();
$data["date"] = $api->param("date");
$data["topic"] = $api->param("topic");
$data["type"] = $api->param("type");
$data["attendee"] = $api->param("attendee");
$data["absent"] = $api->param("absent");
$meeting = Meeting::create($data);

$api->returnSuccess();
?>
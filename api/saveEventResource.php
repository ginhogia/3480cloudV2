<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/event.php");

define('SIZE_LIMIT', 5 * 1024 * 1024);

$api = new Api($_POST);
$api->checkParameter("event_id");
$api->checkParameter("resource_id");
$api->checkParameter("resource_type");
$api->checkParameter("topic");

$page = Page::getPageById(PAGE_ID_TIMELINE, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$club_id = $api->getSession()->getClub()->getId();
$event_id = $api->param("event_id");
$resource_id = $api->param("resource_id");
$type = intval($api->param("resource_type"));
$topic = $api->param("topic");
$link = $api->param("link");

if (count($_FILES) > 0)
{
	$error_no = $_FILES["file"]["error"];
	if ($error_no > 0)
	{
		$api->returnCustomError(1, "File upload error: {$error_no}");
	}

	$original_name = $_FILES["file"]["name"];
	$size = $_FILES["file"]["size"];
	if ($size > SIZE_LIMIT)
	{
		$api->returnCustomError(2, "File size exceeds.");
	}
}
else
{
	if ($type != 3)
		$api->returnCustomError(7, "No file uploaded");
}

$resource = EventResource::getResourceById($resource_id);
if ($resource_id == 0 || !$resource)
{
	// new resource
	$data = array();
	$data["event_id"] = $event_id;
	$data["club_id"] = $club_id;
	$data["type"] = $type;
	$data["topic"] = $topic;
	$data["last_update"] = time();
	$data["fbid"] = $api->getSession()->getUser()->getFBId();
	$data["original_name"] = $type == 3? $link: $original_name;
	$resource = EventResource::create($data);
	if ($resource == null)
	{
		$api->returnCustomError(3, "Unable to create resource in db");
	}
}
else
{
	// update
	$data = array();
	$data["resource_id"] = $resource_id;
	$data["event_id"] = $event_id;
	$data["club_id"] = $club_id;
	$data["type"] = $type;
	$data["topic"] = $topic;
	$data["last_update"] = time();
	$data["fbid"] = $api->getSession()->getUser()->getFBId();
	$data["original_name"] = $type == 3? $link: $original_name;
	if (!$resource->update($data))
	{
		$api->returnCustomError(4, "Unable to update db");
	}
}

if ($type != 3 && count($_FILES) > 0)
{
	$directory = dirname($resource->getPath());
	if (!file_exists($directory) && !mkdir($directory, 0777, true))
	{
		$api->returnCustomError(5, "Cannot create directory.");
	}

	$tmp_name = $_FILES["file"]["tmp_name"];
	if (!move_uploaded_file($tmp_name, $resource->getPath()))
	{
		$api->returnCustomError(6, "Cannot move file.");
	}
}
$api->returnSuccess($resource->getData());
?>
<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/file.php");

define('SIZE_LIMIT', 5 * 1024 * 1024);

$api = new Api($_POST);
$api->checkParameter("file_id");

$page = Page::getPageById(PAGE_ID_PLAN, $api->getSession()->getClub()->getId());
if (!$page->hasOwner($api->getSession()->getUser()))
	$api->returnPermissionDenied();

$file_id = $api->param("file_id");
$club_id = $api->getSession()->getClub()->getId();
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
$file = File::getFileById($file_id, $club_id);
if (!$file)
{
	// new file
	$data = array();
	$data["file_id"] = $file_id;
	$data["club_id"] = $club_id;
	$data["last_update"] = time();
	$data["fbid"] = $api->getSession()->getUser()->getFBId();
	$data["original_name"] = $original_name;
	$file = File::createTemp($data);
}
else
{
	// update
	$file->setLastUpdate(time());
	$file->setFBId($api->getSession()->getUser()->getFBId());
	$file->setOriginalName($original_name);
}

$directory = dirname($file->getPath());
if (!file_exists($directory) && !mkdir($directory, 0777, true))
{
	$api->returnCustomError(3, "Cannot create directory.");
}

$tmp_name = $_FILES["file"]["tmp_name"];
if (!move_uploaded_file($tmp_name, $file->getPath()))
{
	$api->returnCustomError(4, "Cannot move file.");
}

if (!$file->syncDB())
{
	$api->returnCustomError(5, "Cannot sync DB.");
}

$api->returnSuccess();
?>
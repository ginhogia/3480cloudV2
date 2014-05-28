<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/event.php");
$api = new Api($_GET);
$api->checkParameter("id");

$id = $api->param("id");
$club_id = $api->getSession()->getClub()->getId();
if (!$id || $club_id < 0)
{
	header("HTTP/1.0 404 Not Found");
	exit(0);
}

$resource = EventResource::getResourceById($id);
if (!$resource)
{
	header("HTTP/1.0 404 Not Found");
	exit(0);
}

if ($resource->isLink())
{
	header("location: " . $resource->getLink());
	exit(0);
}

header("Content-Description: File Transfer");
if ($resource->isImage())
{
	header("Content-Type: image");
	header("Content-Disposition: filename=" . $resource->getOriginalName());
}
else
{
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=" . $resource->getOriginalName());
}
header("Content-Transfer-Encoding: binary");
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Content-Length: " . $resource->getSize());
ob_clean();
flush();
echo $resource->getContent();
?>
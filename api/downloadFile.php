<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/api.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/file.php");
$api = new Api($_GET);
$api->checkParameter("file_id");

$file_id = $api->param("file_id");
$club_id = $api->getSession()->getClub()->getId();
if (!$file_id || $club_id < 0)
{
	header("HTTP/1.0 404 Not Found");
	exit(0);
}

$file = File::getFileById($file_id, $club_id);
if (!$file)
{
	header("HTTP/1.0 404 Not Found");
	exit(0);
}

header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=" . $file->getOriginalName());
header("Content-Transfer-Encoding: binary");
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Content-Length: " . $file->getSize());
ob_clean();
flush();
echo $file->getContent();
?>
<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/app-info.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/fbsdk/facebook.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/user.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/club.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page.php");

class Session
{
	private $fbid;
	private $user;
	private $page_id;
	private $page;
	private $club;
	
	function __construct()
	{
		$config = array();
		$config["appId"] = AppInfo::appID();
		$config["secret"] = AppInfo::appSecret();
		$config["fileUpload"] = false; // optional
		$facebook = new Facebook($config);
		$this->fbid = $facebook->getUser();

		if ($this->fbid == 0)
			$this->user = User::Guest();
		else
			$this->user = User::getUserByFBId($this->fbid);

		if ($this->user->hasClub())
		{
			if (isset($_GET["view_as_club"]))
				$this->club = Club::getClubById($_GET["view_as_club"]);
			else
				$this->club = Club::getClubById($this->user->getClubId());
		}
		else
		{
			$this->club = Club::NullClub();
		}
	}

	function getUser()
	{
		return $this->user;
	}

	function setPageId($id)
	{
		$this->page_id = $id;
	}

	function getPageId()
	{
		return $this->page_id;
	}

	function getPage()
	{
		if (!$this->page)
		{
			$this->page = Page::getPageById($this->getPageId(), $this->getUser()->getClubId());
		}
		return $this->page;
	}

	function getClub()
	{
		return $this->club;
	}

	function checkPermission()
	{
		if (!$this->getUser()->canViewPage($this->getPageId()))
		{
			header("Location: /");
			exit(0);
		}
	}

	function getData()
	{
		$data = array();
		$data["user"] = $this->getUser()->getData();
		$data["club"] = $this->getClub()->getData();
		$data["page"] = $this->getPage()->getData();
		return $data;
	}
}
?>
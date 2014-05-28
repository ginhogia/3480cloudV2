<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/db.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/user.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/club.php");

class Page
{
	private $id;
	private $club_id;
	private $owner;

	private function __construct($data)
	{
		$this->id = intval($data["id"]);
		$this->club_id = intval($data["club_id"]);
		$this->owner = $data["owner"];
	}

	function getId()
	{
		return $this->id;
	}

	function getData()
	{
		$data = array();
		$data["id"] = $this->id;
		$data["club_id"] = $this->club_id;
		$data["owner"] = $this->owner;
		return $data;		
	}

	function setOwner(array $owner)
	{
		$club = Club::getClubById($this->club_id);
		foreach ($owner as $fbid)
		{
			if (!$club->hasUser($fbid))
				return false;
		}
		$club_id = $this->club_id;
	    $db = new DB();
	    $sql = "delete from page_owner where page_id={$this->id} and club_id={$club_id}";
	    $db->query($sql);
	    foreach ($owner as $fbid)
	    {
	    	$sql = "insert into page_owner(page_id, club_id, fbid) values({$this->id}, {$club_id}, '{$fbid}')";
	    	$db->query($sql);
	    }
	    return true;
	}

	function hasOwner($user)
	{
		return in_array($user->getFBId(), $this->owner);
	}

	public static function getPageById($id, $club_id)
	{
		$data = array();
		$data["id"] = $id;
		$data["club_id"] = $club_id;
		$data["owner"] = array();

		if (!isset($_GET["view_as_club"]))
		{
			$db = new DB();
		    $sql = "select fbid from page_owner where page_id={$id} and club_id={$club_id}";
		    $db->query($sql);
		    while ($result = $db->fetch_array())
			{
				$data["owner"][] = $result["fbid"];
			}
		}
		return new Page($data);
	}
}
?>
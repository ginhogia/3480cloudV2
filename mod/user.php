<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/db.php");
define("USER_ID_GUEST", -1);
define("USER_ID_UNREGISTER", -2);

class User
{
	private $id;
	private $fbid;
	private $name;
	private $club_id;
	private $title;
	private $background;
	private $birth_year;
	private $birth_month;
	private $note;

	function __construct($data)
	{
		$this->id = intval($data["id"]);
		$this->fbid = $data["fbid"];
		$this->name = $data["name"];
		$this->club_id = intval($data["club_id"]);
		$this->title = $data["title"];
		$this->background = intval($data["background"]);
		$this->birth_year = intval($data["birth_year"]);
		$this->birth_month = intval($data["birth_month"]);
		$this->note = $data["note"];
	}

	function isValid()
	{
		return $this->id > 0;
	}

	function canViewPage($id)
	{
		if (!$this->isValid() && $id >= 100)
			return false;
		return true;
	}

	function hasFBId()
	{
		return !is_null($this->fbid);
	}

	function getFacebookUrl()
	{
		return "https://www.facebook.com/" . $this->fbid;
	}

	function getPicUrl()
	{
		return "https://graph.facebook.com/" . $this->fbid . "/picture?type=square";
	}

	function getId()
	{
		return $this->id;
	}

	function getFBId()
	{
		return $this->fbid;
	}

	function getName()
	{
		return $this->name;
	}

	function getClubId()
	{
		return $this->club_id;
	}

	function isGuest()
	{
		return $this->id == USER_ID_GUEST;
	}

	function isUnregisteredUser()
	{
		return $this->id == USER_ID_UNREGISTER;
	}

	function isDistrictTeam()
	{
		return false;
	}

	function hasClub()
	{
		return ($this->club_id >= 0);
	}

	function getData()
	{
		$data = array();
		$data["id"] = $this->id;
		$data["fbid"] = $this->fbid;
		$data["name"] = $this->name;
		$data["club_id"] = $this->club_id;
		$data["title"] = $this->title;
		$data["background"] = $this->background;
		$data["birth_year"] = $this->birth_year;
		$data["birth_month"] = $this->birth_month;
		$data["note"] = $this->note;
		return $data;
	}

	function remove()
	{
		if (!$this->isValid())
			return false;
		$db = new DB();
		if ($this->hasFBId())
		{
			$fbid = $this->fbid;
			$db->query("delete from page_owner where fbid='{$fbid}'");
		}
		$id = $this->id;
		$db->query("delete from user where id={$id}");
		return true;
	}

	function update($data)
	{
		$id = $data["id"];
		$this->fbid = $data["fbid"];
		$this->name = $data["name"];
		$this->title = $data["title"];
		$this->club_id = $data["club_id"];
		$this->background = $data["background"];
		$this->birth_year = $data["birth_year"];
		$this->birth_month = $data["birth_month"];
		$this->note = $data["note"];
		$db = new DB();
		$sql = "update `user` set `fbid`='{$this->fbid}', `title`='{$this->title}', `club_id`='{$this->club_id}', `name`='{$this->name}', `background`={$this->background}, `birth_year`={$this->birth_year}, `birth_month`={$this->birth_month}, `note`='{$this->note}' where `id`={$id};";
		//echo $sql;
		$db->query($sql);
		return true;
	}

	public static function Guest()
	{
		$data = array();
		$data["id"] = USER_ID_GUEST;
		$data["fbid"] = -1;
		$data["name"] = "訪客";
		$data["club_id"] = -1;
		$data["title"] = "";
		return new User($data);
	}

	public static function UnregisteredUser($fbid)
	{
		$data = array();
		$data["id"] = USER_ID_UNREGISTER;
		$data["fbid"] = $fbid;
		$data["name"] = "訪客";
		$data["club_id"] = -1;
		$data["title"] = "";
		return new User($data);
	}

	public static function getUserByFBId($fbid)
	{
		if (!$fbid)
			return false;

		$db = new DB();
		$db->query("select * from user where fbid='{$fbid}'");
		$data = $db->fetch_array();
		if ($data)
		{
			return new User($data);
		}
		else
		{
			return User::UnregisteredUser($fbid);
		}
	}

	public static function getUserById($id)
	{
		if (!$id)
			return null;

		$db = new DB();
		$db->query("select * from user where id='{$id}'");
		$data = $db->fetch_array();
		if ($data)
		{
			return new User($data);
		}
		else
		{
			return null;
		}
	}

	public static function create($data)
	{
		$fbid = $data["fbid"];
		$name = $data["name"];
		$club_id = $data["club_id"];
		$title = $data["title"];
		$background = $data["background"];
		$birth_year = $data["birth_year"];
		$birth_month = $data["birth_month"];
		$note = $data["note"];
		$db = new DB();
		$db->query("insert into user(fbid, name, club_id, title, background, birth_year, birth_month, note) values('{$fbid}','{$name}',{$club_id},'{$title}',{$background},{$birth_year},{$birth_month},'{$note}')");
		$id = $db->get_insert_id();
		$data["id"] = $id;
		return new User($data);
	}
}
?>
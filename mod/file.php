<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/db.php");

class File
{
	private $id;
	private $club_id;
	private $last_update;
	private $fbid;
	private $original_name;
	private $is_temp;

	function __construct($data)
	{
		$this->id = intval($data["file_id"]);
		$this->club_id = intval($data["club_id"]);
		$this->last_update = $data["last_update"];
		$this->fbid = $data["fbid"];
		$this->original_name = $data["original_name"];
		$this->is_temp = false;
	}

	function getPath()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/restrict/file/" . $this->club_id . "/" . $this->id;
	}

	function isExist()
	{
		return file_exists($this->getPath());
	}

	function getSize()
	{
		if (!$this->isExist())
			return 0;
		return filesize($this->getPath());
	}

	function getOriginalName()
	{
		return $this->original_name;
	}

	function getData()
	{
		$data = array();
		$data["file_id"] = $this->id;
		$data["club_id"] = $this->club_id;
		$data["fbid"] = $this->fbid;
		$data["last_update"] = $this->last_update;
		$data["original_name"] = $this->original_name;
		return $data;
	}

	function getContent()
	{
		if ($this->isExist())
			return file_get_contents($this->getPath());
		else
			return "";
	}

	function setLastUpdate($last_update)
	{
		$this->last_update = $last_update;
	}

	function setFBId($fbid)
	{
		$this->fbid = $fbid;
	}

	function setOriginalName($original_name)
	{
		$this->original_name = $original_name;
	}

	function syncDB()
	{
		$file_id = $this->id;
		$club_id = $this->club_id;
		$fbid = $this->fbid;
		$last_update = $this->last_update;
		$original_name = $this->original_name;
		$db = new DB();
		
		if ($this->is_temp)
		{
			$sql = "insert into club_file(file_id, club_id, fbid, last_update, original_name) values({$file_id}, {$club_id}, '{$fbid}', FROM_UNIXTIME({$last_update}), '{$original_name}')";
			if (!$db->query($sql))
				return false;
			$this->is_temp = false;
			return true;
		}
		else
		{
			$sql = "update club_file set fbid='{$fbid}', last_update=FROM_UNIXTIME({$last_update}), original_name='{$original_name}' where file_id={$file_id} and club_id={$club_id}";
			if (!$db->query($sql))
				return false;
			return true;
		}
	}

	function remove()
	{
		if ($this->is_temp)
			return false;
		$db = new DB();
		$file_id = $this->id;
		$club_id = $this->club_id;
		$db->query("delete from club_file where file_id={$file_id} and club_id={$club_id}");
		$this->is_temp = true;
		return true;
	}

	public static function getFileById($id, $club_id)
	{
		if (!$id || !$club_id)
			return null;

		$db = new DB();
		$db->query("select file_id, club_id, fbid, UNIX_TIMESTAMP(last_update) as last_update, original_name from club_file where file_id={$id} and club_id={$club_id}");
		$data = $db->fetch_array();
		if ($data)
		{
			return new File($data);
		}
		else
		{
			return null;
		}
	}

	public static function createTemp($data)
	{
		$file = new File($data);
		$file->is_temp = true;
		return $file;
	}
}
?>
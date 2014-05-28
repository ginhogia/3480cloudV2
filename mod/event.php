<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/db.php");

class Event
{
	private $id;
	private $club_id;
	private $date;
	private $topic;
	private $location;
	private $partner;
	private $note;

	function __construct($data)
	{
		$this->id = intval($data["event_id"]);
		$this->club_id = intval($data["club_id"]);
		$this->date = $data["date"];
		$this->topic = $data["topic"];
		$this->location = $data["location"];
		$this->partner = $data["partner"];
		$this->note = $data["note"];
	}

	function getData()
	{
		$data = array();
		$data["id"] = $this->id;
		$data["club_id"] = $this->club_id;
		$data["date"] = $this->date;
		$data["topic"] = $this->topic;
		$data["location"] = $this->location;
		$data["partner"] = $this->partner;
		$data["note"] = $this->note;
		return $data;
	}

	function getId()
	{
		return $this->id;
	}

	function remove()
	{
		$db = new DB();
		$id = $this->id;
		$db->query("delete from event where event_id={$id}");
		return true;
	}

	function update($data)
	{
		$this->date = $data["date"];
		$this->topic = $data["topic"];
		$this->location = $data["location"];
		$this->partner = $data["partner"];
		$this->note = $data["note"];
		$db = new DB();
		$sql = "update `event` set `date`='{$this->date}', `topic`='{$this->topic}', `location`='{$this->location}', `partner`='{$this->partner}', `note`='{$this->note}' where `event_id`={$this->id};";
		//echo $sql;
		$db->query($sql);
		return true;
	}

	public static function getEventsByClub($club_id)
	{
		$meetings = array();
		if (!$club_id)
			return $meetings;

		$db = new DB();
		$db->query("select event_id, club_id, DATE_FORMAT(date,'%Y/%m/%d') as date, topic, location, partner, note from event where club_id={$club_id}");
		while ($result = $db->fetch_array())
		{
			$events[] = new Event($result);
		}
		return $events;
	}

	public static function getEvent($event_id)
	{
		$db = new DB();
		$db->query("select event_id, club_id, DATE_FORMAT(date,'%Y/%m/%d') as date, topic, location, partner, note from event where event_id={$event_id}");
		$result = $db->fetch_array();
		if ($result)
			return new Event($result);
		else
			return null;
	}

	public static function create($data)
	{
		$club_id = intval($data["club_id"]);
		$date = $data["date"];
		$topic = $data["topic"];
		$location = $data["location"];
		$partner = $data["partner"];
		$note = $data["note"];

		$db = new DB();
		$db->query("insert into event(club_id, date, topic, location, partner, note) values({$club_id},'{$date}','{$topic}','{$location}','{$partner}','{$note}')");
		$data["event_id"] = $db->get_insert_id();
		return new Event($data);
	}
}

class EventResource
{
	private $id;
	private $club_id;
	private $event_id;
	private $type;
	private $topic;
	private $last_update;
	private $fbid;
	private $original_name;

	function __construct($data)
	{
		$this->id = intval($data["resource_id"]);
		$this->club_id = intval($data["club_id"]);
		$this->event_id = intval($data["event_id"]);
		$this->type = intval($data["type"]);
		$this->topic = $data["topic"];
		$this->last_update = $data["last_update"];
		$this->fbid = $data["fbid"];
		$this->original_name = $data["original_name"];
	}

	function getPath()
	{
		return $_SERVER["DOCUMENT_ROOT"] . "/restrict/event_resource/" . $this->club_id . "/" . $this->event_id . "/" . $this->id;
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
		$data["id"] = $this->id;
		$data["club_id"] = $this->club_id;
		$data["event_id"] = $this->event_id;
		$data["type"] = $this->type;
		$data["topic"] = $this->topic;
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

	function isLink()
	{
		return $this->type == 3;
	}

	function isImage()
	{
		return $this->type == 2;
	}

	function getLink()
	{
		$link = $this->original_name;
		if (stripos($link, "http://") === 0 || stripos($link, "https://") === 0)
			return $link;
		else
			return "http://" . $link;
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

	function update($data)
	{
		$this->club_id = intval($data["club_id"]);
		$this->event_id = intval($data["event_id"]);
		$this->type = intval($data["type"]);
		$this->topic = $data["topic"];
		$this->last_update = $data["last_update"];
		$this->fbid = $data["fbid"];
		$this->original_name = $data["original_name"];
		$resource_id = $this->id;
		$club_id = $this->club_id;
		$event_id = $this->event_id;
		$type = $this->type;
		$topic = $this->topic;
		$fbid = $this->fbid;
		$last_update = $this->last_update;
		$original_name = $this->original_name;
		$db = new DB();
		
		$sql = "update event_resource set topic='{$topic}', fbid='{$fbid}', last_update=FROM_UNIXTIME({$last_update}), original_name='{$original_name}' where resource_id={$resource_id}";
		if (!$db->query($sql))
			return false;
		return true;
	}

	function remove()
	{
		$db = new DB();
		$resource_id = $this->id;
		$db->query("delete from event_resource where resource_id={$resource_id}");
		return true;
	}

	public static function getResourcesByEvent($event_id)
	{
		$resources = array();
		if (!$event_id)
			return $resources;

		$db = new DB();
		$db->query("select resource_id, club_id, event_id, type, topic, fbid, UNIX_TIMESTAMP(last_update) as last_update, original_name from event_resource where event_id={$event_id}");
		while ($result = $db->fetch_array())
		{
			$resources[] = new EventResource($result);
		}
		return $resources;
	}

	public static function getResourceById($id)
	{
		if (!$id)
			return null;

		$db = new DB();
		$db->query("select resource_id, club_id, event_id, type, topic, fbid, UNIX_TIMESTAMP(last_update) as last_update, original_name from event_resource where resource_id={$id}");
		$data = $db->fetch_array();
		if ($data)
		{
			return new EventResource($data);
		}
		else
		{
			return null;
		}
	}

	public static function create($data)
	{
		$club_id = $data["club_id"];
		$event_id = $data["event_id"];
		$type = $data["type"];
		$topic = $data["topic"];
		$fbid = $data["fbid"];
		$last_update = $data["last_update"];
		$original_name = $data["original_name"];
		
		$db = new DB();
		$sql = "insert into event_resource(club_id, event_id, type, topic, fbid, last_update, original_name) values({$club_id}, {$event_id}, {$type}, '{$topic}', '{$fbid}', FROM_UNIXTIME({$last_update}), '{$original_name}')";

		if (!$db->query($sql))
			return null;
		$data["resource_id"] = $db->get_insert_id();
		$resource = new EventResource($data);
		return $resource;
	}
}
?>
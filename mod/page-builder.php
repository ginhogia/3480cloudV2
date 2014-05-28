<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/app-info.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/session.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page-id.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/mod/page.php");

class PageBuilder
{
  private $session;
  private $data;

  function __construct(Session $session, $data = null)
  {
    $this->session = $session;
    $this->data = $data;
  }

	function outputHead()
	{
		$text = <<<_T
  <head>
    <meta charset="utf-8">
    <title>國際扶輪3480地區扶輪青年服務團 - 雲端服務中心</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="zinien@gmail.com">
    
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/page.css" rel="stylesheet">
    <link href="css/bootstrap-responsive.css" rel="stylesheet">

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/object.js"></script>
    <script type="text/javascript">
      (function(w)
      {
        var r = {};
        r.fbAppId = "%FB_APP_ID%";
        r.session = new Session(%SESSION%);
        r.data = %DATA%;
        w._r = r;
        console.log(w._r);
      })(window);
    </script>
    <script src="js/loader.js"></script>
  </head>
_T;
		$text = str_replace("%FB_APP_ID%", AppInfo::appID(), $text);
    $text = str_replace("%SESSION%", json_encode($this->session->getData()), $text);
    $text = str_replace("%DATA%", json_encode($this->data), $text);
		echo $text;
	}

	function outputNavBar()
	{
		$text = <<<_T
    <!-- begin navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">RAC3480 雲端服務中心</a>
          <div class="nav-collapse collapse">
_T;
		if ($this->session->getUser()->isValid())
		{
			$text .= <<<_T
            <p class="navbar-text pull-right" id="user_info">
              <a href="%USER_FB_URL%" target="_blank" class="navbar-link"><img src="%USER_PIC_URL%" />%USER_NAME%</a> / <span class="%IS_VIEW_AS%">%USER_CLUB_NAME%</span> %USER_DISTRICT_TEAM%, <a href="#" class="navbar-link" data-link="logout">登出</a>
            </p>
_T;

		  $text = str_replace("%USER_FB_URL%", $this->session->getUser()->getFacebookUrl(), $text);
  		$text = str_replace("%USER_PIC_URL%", $this->session->getUser()->getPicUrl(), $text);
  		$text = str_replace("%USER_NAME%", $this->session->getUser()->getName(), $text);
  		$text = str_replace("%USER_CLUB_NAME%", $this->session->getClub()->getName(), $text);
  		$text = str_replace("%USER_DISTRICT_TEAM%", $this->session->getUser()->isDistrictTeam()? "/ 地區團隊": "", $text);
      $text = str_replace("%IS_VIEW_AS%", isset($_GET["view_as_club"])? "view-as": "", $text);
		}
		else if ($this->session->getUser()->isGuest())
			$text .= <<<_T
            <p class="navbar-text pull-right">
              <a href="#" class="navbar-link" data-link="login">使用 Facebook 帳號登入</a>
            </p>
_T;
    else if ($this->session->getUser()->isUnregisteredUser())
    {
      $text .= <<<_T
            <p class="navbar-text pull-right" id="user_info">
              <a href="%USER_FB_URL%" target="_blank" class="navbar-link"><img src="%USER_PIC_URL%" /><fb:name uid="loggedinuser" useyou="false" linked="false" /></a> (尚未登錄所屬團), <a href="#" class="navbar-link" data-link="logout">登出</a>
            </p>
_T;

      $text = str_replace("%USER_FB_URL%", $this->session->getUser()->getFacebookUrl(), $text);
      $text = str_replace("%USER_PIC_URL%", $this->session->getUser()->getPicUrl(), $text);
    }
		$text .= <<<_T
            <ul class="nav">
              <li class="active"><a href="#">各項服務</a></li>
              <li><a href="https://docs.google.com/forms/d/16LTn2D-tU44cEJ0Tz3fvE-GNGmKfjnkAOkQqd-oQ1RQ" target="_blank">問題回報</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- end navbar -->
_T;
		echo $text;
	}

	function outputMenu()
	{
		$text = <<<_T
        <!-- begin menu -->
        <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">地區資訊</li>
_T;
		$text .= $this->generateNavItem("/", "最新消息", PAGE_ID_INDEX);
		$text .= $this->generateNavItem("/calendar.php", "行事曆", PAGE_ID_CALENDAR);
    $text .= $this->generateNavItem("/download.php", "文件下載", PAGE_ID_DOWNLOAD);
		$text .= $this->generateNavItem("https://www.facebook.com/RAC3480", "Facebook專頁", PAGE_ID_VOID, true);
		if ($this->session->getUser()->isValid())
		{
			$text .= <<<_T
              <li class="nav-header">各團功能區</li>
_T;
			//$text .= $this->generateNavItem("/club.php", "社團資料登錄", PAGE_ID_CLUB);
			$text .= $this->generateNavItem("/member.php?" . $_SERVER["QUERY_STRING"], "團員管理", PAGE_ID_MEMBER);
			$text .= $this->generateNavItem("/plan.php?" . $_SERVER["QUERY_STRING"], "服務計畫提交", PAGE_ID_PLAN);
      $text .= $this->generateNavItem("/schedule.php?" . $_SERVER["QUERY_STRING"], "年度行事曆登錄", PAGE_ID_SCHEDULE);
      $text .= $this->generateNavItem("/timeline.php?" . $_SERVER["QUERY_STRING"], "例會/活動回顧", PAGE_ID_TIMELINE);
			//$text .= $this->generateNavItem("/meeting.php", "例會/活動資料登錄", PAGE_ID_MEETING);
			//$text .= $this->generateNavItem("/attendance.php", "例會出席登錄", PAGE_ID_ATTENDANCE);
		}
		$text .= <<<_T
            </ul>
          </div>
        </div>
        <!-- end menu -->
_T;
		echo $text;
	}

	private function generateNavItem($link, $name, $id, $target_blank = false)
	{
		if (!$this->session->getUser()->canViewPage($id))
			return "";

		$text = <<<_T
              <li class="%CLASS%"><a href="%LINK%" %TARGET%>%NAME%</a></li>
_T;
		$text = str_replace("%CLASS%", ($id == $this->session->getPageId())? "active" : "", $text);
		$text = str_replace("%LINK%", $link, $text);
		$text = str_replace("%NAME%", $name, $text);
		$text = str_replace("%TARGET%", $target_blank? "target='_blank'": "", $text);
		return $text;
	}

	function outputFooter()
	{
		echo <<<_T
      <!-- begin footer -->
      <hr>
      <footer>
      <p>&copy; 國際扶輪3480地區扶輪青年服務團</p>
      </footer>
      <!-- end footer -->
_T;
	}

	function outputPageInfo()
	{
		$text = <<<_T
          <div class="well well-small" id="page_info">
            頁面管理員：<span data-ref="owner"></span>
            <span class="pull-right" data-visible="owner"><a href="#" data-link="edit">編輯</a></span>
            
            <!-- begin modal -->
            <div class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" data-ref="editor">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4>頁面設定</h4>
              </div>
              <div class="modal-body">
                <form class="form-horizontal">
                  <div class="control-group">
                    <label class="control-label" for="page_owner">頁面管理員</label>
                    <div class="controls height-min-120">
                      <div data-ref="owner">
                        <input type="text" placeholder="輸入團員nickname以新增" />
                        <p class="template" data-src=""><span data-ref="name"></span> <a href="#" class="close pull-right" data-link="remove">&times;</a></p>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
                <a href="#" class="btn btn-primary" data-link="save">確定</a>
              </div>
            </div>
            <!-- end modal -->
          </div>
          <script>
$(document).ready(function()
{
  var page_info = new PageInfo($("#page_info"));
  page_info.setData(_r.session);
});
          </script>
_T;
		//$text = str_replace("%PAGE_DATA%", $this->session->getPage()->generateJSON(), $text);
		echo $text;
	}
}
?>
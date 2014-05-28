<?
require_once("mod/session.php");
require_once("mod/page-builder.php");
require_once("mod/meeting.php");

$session = new Session();
$session->setPageId(PAGE_ID_SCHEDULE);
$session->checkPermission();
$meetings = Meeting::getMeetingsByClub($session->getClub()->getId());
$data = array();
foreach ($meetings as $meeting)
{
  $data[] = $meeting->getData();
}

$builder = new PageBuilder($session, $data);
?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="zh-TW">

<? $builder->outputHead(); ?>

  <body>
    <div id="fb-root"></div>

<? $builder->outputNavBar(); ?>

    <div class="container-fluid">
      <div class="row-fluid">

<? $builder->outputMenu(); ?>

<style>
.member-area
{
  min-height: 24px;
}
.member-area li
{
  margin: 2px;
}
.member-area li.template
{
  display: none;
}
</style>
<link href="http://code.jquery.com/ui/1.10.3/themes/flick/jquery-ui.css" rel="stylesheet">
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script>
var MeetingList = function(element)
{
  var me = this;
  me.element = {};
  me.element.root = $(element);
  me.element.list = me.element.root.find("tbody");
  me.element.editor = me.element.root.find("> [data-ref='editor']");
  me.element.attendance_editor = me.element.root.find("> [data-ref='attendance-editor']");
  me.data = {};

  me.setData = function(data)
  {
  	me.data = [];
  	for (var i = 0; i < data.length; ++i)
  		me.data.push(new Meeting(data[i]));
    me.refresh();
  };

  me.getMeetingById = function(id)
  {
  	id = parseInt(id);
    for (var i = 0; i < me.data.length; ++i)
    {
      if (me.data[i].id == id)
        return me.data[i];
    }
    return null;
  };

  me.refresh = function()
  {
    var addMeeting = function(meeting)
    {
      var $meeting = me.element.list.find("tr.template").clone();
      $meeting.removeClass("template");
      $meeting.attr("data-src", meeting.id);
      $meeting.find("[data-ref='id']").text(meeting.id);
      $meeting.find("[data-ref='date']").text(meeting.date);
      $meeting.find("[data-ref='topic']").text(meeting.topic);
      $meeting.find("[data-ref='type']").text(meeting.getTypeString());
      $meeting.find("[data-ref='attendance-rate']").text(meeting.getAttendanceRate());

      $meeting.find("[data-link='remove']").click(function(e)
      {
        var id = $(e.target).parents("tr").attr("data-src");
        var meeting = me.getMeetingById(id);
        if (confirm("確定要刪除 第 " + meeting.id + " 次例會：" + meeting.topic + " 嗎?"))
        {
          meeting.remove(function(result)
          {
            console.log(result);
            if (result.code != 0)
            {
              alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
              return;
            }
            var index = $.inArray(meeting, me.data);
            if (index == -1)
              return;
            me.data.splice(index, 1);
            console.log(me.data);
            $(e.target).parents("tr").remove();
          });
        }
      });

      $meeting.find("[data-link='edit']").click(function(e)
      {
      	var id = $(e.target).parents("tr").attr("data-src");
        me.showEditor(me.getMeetingById(id));
      });

      $meeting.find("[data-link='edit-attendance']").click(function(e)
      {
        var id = $(e.target).parents("tr").attr("data-src");
        me.showAttendanceEditor(me.getMeetingById(id));
      });

      me.element.list.append($meeting);
    };

    me.element.list.find("tr:not(.template)").remove();

    me.data.sort(function(a, b)
    {
      return a.id - b.id;
    });
    for (var i = 0; i < me.data.length; ++i)
    {
      addMeeting(me.data[i]);
    }
  };

  me.showEditor = function(meeting)
  {
    me.element.editor.attr("data-src", meeting.id);
    me.element.editor.find("[data-ref='id']").val(meeting.id);
    me.element.editor.find("[data-ref='date']").val(meeting.date);
    var picker = $("#meeting_date").datepicker({dateFormat:"yy/mm/dd"});
    picker.on("changeDate", function(e)
    {
      picker.datepicker("hide");
    });
    me.element.editor.find("[data-ref='topic']").val(meeting.topic);
    me.element.editor.find("[data-ref='type']").val(meeting.type);
    me.element.editor.modal();
  };

  var showEditorNew = function()
  {
  	var meeting = new Meeting();
  	if (me.data.length > 0)
  	{
  		meeting.id = me.data[me.data.length - 1].id + 1;
  	}
  	me.data.push(meeting);
  	me.showEditor(meeting);
  };

  me.element.editor.find("[data-link='save']").click(function()
  {
    var id = parseInt(me.element.editor.attr("data-src"));
    var new_id = parseInt(me.element.editor.find("[data-ref='id']").val());
    if (id != new_id && me.getMeetingById(new_id))
    {
    	alert("例會編號重複，請檢查。");
    	me.element.editor.find("[data-ref='id']").focus();
    	return;
    }

    if (me.element.editor.find("[data-ref='topic']").val().length == 0)
    {
    	alert("未填寫例會名稱。");
    	me.element.editor.find("[data-ref='topic']").focus();
    	return;
    }

    var meeting = me.getMeetingById(id);
    meeting.id = parseInt(me.element.editor.find("[data-ref='id']").val());
    meeting.date = me.element.editor.find("[data-ref='date']").val();
    meeting.topic = me.element.editor.find("[data-ref='topic']").val();
    meeting.type = parseInt(me.element.editor.find("[data-ref='type']").val());
    meeting.save(id, function(result)
    {
      console.log(result);
      if (result.code != 0)
      {
        alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
        return;
      }
      me.element.editor.modal('hide');
      me.refresh();
    });
  });

  me.showAttendanceEditor = function(meeting)
  {
    me.element.attendance_editor.data("src", meeting.id);
    me.element.attendance_editor.find("[data-ref='id']").text(meeting.id);
    me.element.attendance_editor.find("[data-ref='topic']").text(meeting.topic);

    me.element.attendance_editor.find("li:not(.template)").remove();
    $template = me.element.attendance_editor.find("li.template");
    $container = me.element.attendance_editor.find("[data-ref='attendee']");
    for (var i = 0; i < meeting.attendee.length; ++i)
    {
      $item = $template.clone();
      $item.removeClass("template");
      $item.data("src", meeting.attendee[i]);
      var user = _r.session.club.getUserById(meeting.attendee[i]);
      if (!user)
        continue;

      $item.text(user.name);
      $container.append($item);
    }
    $container = me.element.attendance_editor.find("[data-ref='absent']");
    for (var i = 0; i < meeting.absent.length; ++i)
    {
      $item = $template.clone();
      $item.removeClass("template");
      $item.data("src", meeting.absent[i]);
      var user = _r.session.club.getUserById(meeting.absent[i]);
      if (!user)
        continue;

      $item.text(user.name);
      $container.append($item);
    }
    $container = me.element.attendance_editor.find("[data-ref='unreg']");
    var member = _r.session.club.member;
    for (var i = 0; i < member.length; ++i)
    {
      if (meeting.attendee.indexOf(member[i].id) >= 0 || meeting.absent.indexOf(member[i].id) >= 0)
        continue;

      $item = $template.clone();
      $item.removeClass("template");
      $item.data("src", member[i].id);
      $item.text(member[i].name);
      $container.append($item);      
    }

    var updateRate = function()
    {
      var attendee = me.element.attendance_editor.find("[data-ref='attendee'] li").length;
      var absent = me.element.attendance_editor.find("[data-ref='absent'] li").length;
      var rate;
      if (attendee + absent)
        rate = parseInt(attendee * 100.0 / (attendee + absent)) + "%";
      else
        rate = "尚未登錄";
      me.element.attendance_editor.find("[data-ref='rate']").text(rate);
    }

    me.element.attendance_editor.find(".member-area").sortable({connectWith:".member-area", update: updateRate}).disableSelection();
    me.element.attendance_editor.modal();
    updateRate();
  };

  me.element.attendance_editor.find("[data-link='save']").click(function()
  {
    var id = me.element.attendance_editor.data("src");
    var meeting = me.getMeetingById(id);
    meeting.attendee = [];
    var $attendee = me.element.attendance_editor.find("[data-ref='attendee'] li");
    for (var i = 0; i < $attendee.length; ++i)
    {
      meeting.attendee.push($($attendee[i]).data("src"));
    }
    meeting.absent = [];
    var $absent = me.element.attendance_editor.find("[data-ref='absent'] li");
    for (var i = 0; i < $absent.length; ++i)
    {
      meeting.absent.push($($absent[i]).data("src"));
    }

    meeting.save(id, function(result)
    {
      console.log(result);
      if (result.code != 0)
      {
        alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
        return;
      }
      me.element.attendance_editor.modal('hide');
      me.refresh();
    });
  });

  me.element.root.find("[data-link='add']").click(showEditorNew);
};

$(document).ready(function()
{
	var meeting_list = new MeetingList($("#page_content"));
	meeting_list.setData(_r.data);
});
</script>
        <!-- begin content -->
        <div class="span9" id="page_content">
          <h1>年度行事曆登錄</h1>
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>例會編號</th>
                <th>例會日期</th>
                <th>例會名稱</th>
                <th>例會分類</th>
                <th colspan="3">功能</th>
              </tr>
            </thead>
            <tbody>
              <tr class="template">
                <td data-ref="id"></td>
                <td data-ref="date"></td>
                <td data-ref="topic"></td>
                <td data-ref="type"></td>
                <td><a href="#" data-link="edit" data-visible="owner"><i class="icon-pencil"></i> 修改</a></td>
                <td><a href="#" data-link="remove" data-visible="owner"><i class="icon-remove"></i> 刪除</a></td>
                <td><a href="#" data-link="edit-attendance"><i class="icon-list-alt"></i> 出席登錄</a> (<span data-ref="attendance-rate"></span>)</td>
              </tr>
            </tbody>
          </table>
          <p class="pull-right" data-visible="owner">
            <a id="create_meeting" class="btn btn-primary" href="#" data-link="add">新增</a>
          </p>
          <p class="clearfix"></p>

          <!-- begin modal -->
          <div class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" data-ref="editor">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4>例會資訊</h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal">
                <div class="control-group">
                  <label class="control-label" for="meeting_id">例會編號</label>
                  <div class="controls">
                    <input type="number" id="meeting_id" data-ref="id" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label" for="meeting_date">例會日期</label>
                  <div class="controls">
                    <input type="text" id="meeting_date" data-ref="date" data-date-format="yyyy/mm/dd" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label" for="meeting_topic">例會名稱</label>
                  <div class="controls">
                    <input type="text" id="meeting_topic" data-ref="topic" />
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label" for="meeting_type">例會分類</label>
                  <div class="controls">
                    <select class="input-medium" id="meeting_type" data-ref="type">
                      <option value="1">團務</option>
                      <option value="2">國際</option>
                      <option value="3">職業</option>
                      <option value="4">社服</option>
                      <option value="5">公關</option>
                      <option value="6">其他</option>
                    </select>
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

          <!-- begin modal -->
          <div class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" data-ref="attendance-editor">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4>出席登錄：(<span data-ref="id"></span>) <span data-ref="topic"></span></h4>
            </div>
            <div class="modal-body">
            未登錄/免記出席
            <ul class="inline member-area well well-small" data-ref="unreg">
              <li class="template btn"></li>
            </ul>
            出席
            <ul class="inline member-area well well-small" data-ref="attendee">
            </ul>
            未出席
            <ul class="inline member-area well well-small" data-ref="absent">
            </ul>
            </div>
            <div class="modal-footer">
              <div class="pull-left">
                出席率：<span data-ref="rate"></span>
              </div>
              <button class="btn" data-dismiss="modal" aria-hidden="true">取消</button>
              <a href="#" class="btn btn-primary" data-visible="owner" data-link="save">確定</a>
            </div>
          </div>
          <!-- end modal -->

<? $builder->outputPageInfo(); ?>

        </div><!--/.span9-->
        <!-- end content -->

      </div><!--/.row-fluid-->

<? $builder->outputFooter(); ?>

    </div><!--/.container-fluid-->
  </body>
</html>

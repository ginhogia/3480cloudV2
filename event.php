<?
require_once("mod/session.php");
require_once("mod/page-builder.php");
require_once("mod/event.php");

$session = new Session();
$session->setPageId(PAGE_ID_TIMELINE);
$session->checkPermission();
$event = Event::getEvent(intval($_GET["id"]));
$resources = EventResource::getResourcesByEvent($_GET["id"]);
$data = array();
$data["event"] = $event? $event->getData(): null;
$data["resources"] = array();
foreach ($resources as $resource)
{
  $data["resources"][] = $resource->getData();
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
#resource
{
  display: none;
}
a[data-link='upload']
{
  position: relative;
  overflow: hidden;
  display: inline-block;
}
input[type='file']
{
  position: absolute;
  top: 0;
  right: 0;
  margin: 0;
  opacity: 0;
  display: block;
  cursor: pointer;
}
</style>
<link href="css/datepicker.css" rel="stylesheet">
<script src="js/jquery.form.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script><script>
var EventEditor = function(element)
{
  var me = this;
  me.element = {};
  me.element.root = $(element);
  me.element.basic = me.element.root.find("#basic");
  me.element.resource = me.element.root.find("#resource");
  me.element.resource_list = me.element.resource.find("tbody");
  me.element.remove_button = me.element.basic.find("[data-link='remove']");
  me.element.editor = me.element.root.find("[data-ref='editor']");
  me.element.editor_form = me.element.editor.find("form");

  me.data = {};

  me.setData = function(data)
  {
    console.log(data);
  	me.data.event = new Event(data.event);
    me.data.resources = [];
    for (var i = 0; i < data.resources.length; ++i)
    {
      me.data.resources.push(new EventResource(data.resources[i]));
    }
    me.refresh();
  };

  me.refresh = function()
  {
    me.element.basic.find("[data-ref='date']").val(me.data.event.date);
    me.element.basic.find("[data-ref='topic']").val(me.data.event.topic);
    me.element.basic.find("[data-ref='location']").val(me.data.event.location);
    me.element.basic.find("[data-ref='partner']").val(me.data.event.partner);
    me.element.basic.find("[data-ref='note']").val(me.data.event.note);
    if (me.data.event.id)
    {
      me.element.resource.show();
      me.element.remove_button.show();
    }
    else
    {
      me.element.remove_button.hide();
    }

    var addResource = function(resource)
    {
      var $resource = me.element.resource_list.find("tr.template").clone();
      $resource.removeClass("template");
      $resource.attr("data-src", resource.id);
      $resource.find("[data-ref='type']").text(resource.getTypeString());
      $resource.find("[data-ref='topic'] > a").text(resource.topic).attr("href", "/api/getEventResource.php?id=" + resource.id);
      //$resource.find("[data-link='edit']").attr("href", "event.php?id=" + event.id);
      var user = _r.session.club.getUserByFBId(resource.fbid);
      if (user)
        $resource.find("[data-ref='user']").text(user.name);
      else
        $resource.find("[data-ref='user']").text("未知的使用者 " + resource.fbid);
      $resource.find("[data-ref='date']").text(new Date(resource.last_update * 1000).toLocaleString());

      $resource.find("[data-link='remove']").click(function(e)
      {
        e.preventDefault();
        var id = $(e.target).parents("tr").attr("data-src");
        var resource = me.getResourceById(id);
        if (confirm("確定要刪除 " + resource.topic + " 嗎?"))
        {
          resource.remove(function(result)
          {
            console.log(result);
            if (result.code != 0)
            {
              alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
              return;
            }
            var index = $.inArray(resource, me.data.resources);
            if (index == -1)
              return;
            me.data.resources.splice(index, 1);
            console.log(me.data.resources);
            $(e.target).parents("tr").remove();
          });
        }
      });

      me.element.resource_list.append($resource);
    };

    me.data.resources.sort(function(a, b)
    {
      return new Date(a.last_update) - new Date(b.last_update);
    });
    me.element.resource_list.find("tr:not(.template)").remove();
    for (var i = 0; i < me.data.resources.length; ++i)
    {
      addResource(me.data.resources[i]);
    }
  };

  me.getResourceById = function(id)
  {
    for (var i = 0; i < me.data.resources.length; ++i)
    {
      if (me.data.resources[i].id == id)
        return me.data.resources[i];
    }
    return null;
  };

  var picker = me.element.basic.find("[data-ref='date']").datepicker();
  picker.on("changeDate", function(e)
  {
    picker.datepicker("hide");
  });

  me.element.remove_button.click(function(e)
  {
    if (confirm("確定要刪除 " + me.data.event.topic + " 嗎?"))
    {
      me.data.event.remove(function(result)
      {
        console.log(result);
        if (result.code != 0)
        {
          alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
          return;
        }
        location.href = "timeline.php";
      });
    }
  });

  me.element.basic.find("[data-link='save']").click(function(e)
  {
    if (me.element.basic.find("[data-ref='topic']").val() == "")
    {
      alert("請填入例會/活動名稱。");
      me.element.basic.find("[data-ref='topic']").focus();
      return;
    }
    if (me.element.basic.find("[data-ref='date']").val() == "")
    {
      alert("請填入例會/活動日期。");
      me.element.basic.find("[data-ref='date']").focus();
      return;
    }

    me.data.event.date = me.element.basic.find("[data-ref='date']").val();
    me.data.event.topic = me.element.basic.find("[data-ref='topic']").val();
    me.data.event.location = me.element.basic.find("[data-ref='location']").val();
    me.data.event.partner = me.element.basic.find("[data-ref='partner']").val();
    me.data.event.note = me.element.basic.find("[data-ref='note']").val();
    console.log(me.data);
    me.data.event.save(function(result)
    {
      console.log(result);
      if (result.code != 0)
      {
        alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
        return;
      }
      me.data.event.id = result.id;
      alert("儲存完成。");
      me.element.resource.show();
      me.element.remove_button.show();
    });
  });

  var applyResourceType = function()
  {
    var url = me.element.editor.find("div.url");
    var file = me.element.editor.find("div.file");
    switch(parseInt(me.element.editor.find("input[name='resource_type']:checked").val()))
    {
      case 1:
      case 2:
        url.hide();
        file.show();
        break;
      case 3:
        url.show();
        file.hide();
        break;
    }
  };

  me.showEditor = function(resource)
  {
    me.element.editor.attr("data-src", resource.id);
    me.element.editor.find("input[name='resource_id']").val(resource.id);
    me.element.editor.find("input[name='event_id']").val(resource.event_id);
    me.element.editor.find("input[name='resource_type'][value='" + resource.type + "']").attr("checked", true);
    me.element.editor.find("input[name='topic']").val(resource.topic);
    me.element.editor.find("input[name='link']").val(resource.link);
    applyResourceType();
    console.log(resource);
    me.element.editor.modal();
  };

  me.showEditorNew = function()
  {
    var resource = new EventResource();
    resource.event_id = me.data.event.id;
    me.showEditor(resource);
  };

  me.element.editor.find("[data-ref='type']").change(applyResourceType);

  me.element.editor.find("[data-link='save']").click(function()
  {
    var id = parseInt(me.element.editor.attr("data-src"));

    if (me.element.editor.find("[data-ref='topic']").val().length == 0)
    {
      alert("未填寫說明。");
      me.element.editor.find("[data-ref='topic']").focus();
      return;
    }
    if (me.element.editor.find("[data-ref='link']").val().length == 0 && parseInt(me.element.editor.find("input[name='resource_type']:checked").val()) == 3)
    {
      alert("未填寫網址。");
      me.element.editor.find("[data-ref='link']").focus();
      return;
    }

    me.element.editor_form.ajaxSubmit({
    url: "/api/saveEventResource.php",
    type: "POST",
    error: function(e)
    {
      alert("啊，壞掉了！\n\n錯誤碼：" + e);
    },
    success: function(e)
    {
      var result = $.parseJSON(e);
      console.log(result);
      if (result.code != 0)
      {
        if (result.code == -1007)
        {
          alert("尚未選擇檔案。");
          return;
        }
        alert("啊，壞掉了！\n\n錯誤碼：" + result.code);
        return;
      }
      var resource = new EventResource(result);
      me.data.resources.push(resource);
      me.refresh();
      me.element.editor.modal("hide");
    }
    });
  });

  me.element.resource.find("[data-link='add']").click(function()
  {
    me.showEditorNew();
  });

};

$(document).ready(function()
{
	var event_editor = new EventEditor($("#page_content"));
	event_editor.setData(_r.data);
});
</script>
        <!-- begin content -->
        <div class="span9" id="page_content">
          <h1>例會/活動回顧</h1>
          <div id="basic">
            <input type="text" class="input-xxlarge" data-ref="topic" placeholder="名稱" /><br />
            <input type="text" data-ref="date" data-date-format="yyyy/mm/dd" placeholder="日期" /><br />
            <h4>相關資訊</h4>
            <input type="text" class="input-xxlarge" data-ref="location" placeholder="地點" /><br />
            <input type="text" class="input-xxlarge" data-ref="partner" placeholder="合作單位/主講人" /><br />
            <textarea data-ref="note" rows="4" class="input-xxlarge" placeholder="其他資訊"></textarea><br />

            <a class="btn btn-link" href="timeline.php" data-link="cancel">&laquo; 回到例會/活動列表</a>
            <p class="pull-right" data-visible="owner">
              <a class="btn btn-danger" href="#" data-link="remove"><i class="icon-trash icon-white"></i> 刪除本次回顧</a>
              <a class="btn btn-primary" href="#" data-link="save">儲存資訊</a>
            </p>
          </div>

          <div id="resource">
            <hr class="clearfix" />
            <h4>檔案/照片/連結管理</h4>
            <table class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>類型</th>
                  <th>說明</th>
                  <th>更新日期</th>
                  <th>更新使用者</th>
                  <th>功能</th>
                </tr>
              </thead>
              <tbody>
                <tr class="template">
                  <td data-ref="type"></td>
                  <td data-ref="topic"><a href="#" target="_blank"></a></td>
                  <td data-ref="date"></td>
                  <td data-ref="user"></td>
                  <!--<td><a href="#" data-link="edit" data-visible="owner"><i class="icon-pencil"></i> 修改</a></td>-->
                  <td><a href="#" data-link="remove" data-visible="owner"><i class="icon-remove"></i> 刪除</a></td>
                </tr>
              </tbody>
            </table>
            <p class="pull-right" data-visible="owner">
              <a class="btn" data-visible="owner" data-link="add">新增</a>
            </p>
            <p class="clearfix"></p>
          </div>
          
          <!-- begin modal -->
          <div class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" data-ref="editor">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4>檔案/照片/連結資訊</h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal">
                <input type="hidden" name="resource_id" value="0" />
                <input type="hidden" name="event_id" value="0" />
                <div class="control-group">
                  <label class="control-label">類型</label>
                  <div class="controls">
                    <label class="radio inline"><input type="radio" name="resource_type" data-ref="type" value="1" />檔案</label>
                    <label class="radio inline"><input type="radio" name="resource_type" data-ref="type" value="2" />照片</label>
                    <label class="radio inline"><input type="radio" name="resource_type" data-ref="type" value="3" />連結</label>
                  </div>
                </div>
                <div class="control-group">
                  <label class="control-label" for="resource_topic">說明</label>
                  <div class="controls">
                    <input type="text" class="input-xlarge" id="resource_topic" name="topic" data-ref="topic" />
                  </div>
                </div>
                <div class="control-group url">
                  <label class="control-label" for="resource_link">網址</label>
                  <div class="controls">
                    <input type="text" class="input-xlarge" id="resource_link" name="link" data-ref="link" />
                  </div>
                </div>
                <div class="control-group file">
                  <label class="control-label" for="resource_file">檔案</label>
                  <div class="controls">
                    <a href="#" data-link="upload" class="btn"><input type="file" name="file" />選擇檔案</a>
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

<? //$builder->outputPageInfo(); ?>

        </div><!--/.span9-->
        <!-- end content -->

      </div><!--/.row-fluid-->

<? $builder->outputFooter(); ?>

    </div><!--/.container-fluid-->
  </body>
</html>

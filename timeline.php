<?
require_once("mod/session.php");
require_once("mod/page-builder.php");
require_once("mod/event.php");

$session = new Session();
$session->setPageId(PAGE_ID_TIMELINE);
$session->checkPermission();
$events = Event::getEventsByClub($session->getClub()->getId());
$data = array();
foreach ($events as $event)
{
  $data[] = $event->getData();
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
div.event
{
  height: 100px;
  margin-bottom: 10px;
}
div.event > .date
{
  position: relative;
  top: -60px;
  line-height: 60px;
  color: white;
  padding: 0 10px;
  margin: 0;
  font-size: 60px;
  text-shadow: 1px 1px 2px rgba(150, 150, 150, 1);
}
div.event > a
{
  text-decoration: none;
}
div.event h3
{
  position: relative;
  line-height: 40px;
  top: -100px;
  color: white;
  padding: 0 10px;
  margin: 0;
  text-align: right;
  /* IE9 SVG, needs conditional override of 'filter' to 'none' */
  background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzAwMDAwMCIgc3RvcC1vcGFjaXR5PSIwIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMwMDAwMDAiIHN0b3Atb3BhY2l0eT0iMC43Ii8+CiAgPC9saW5lYXJHcmFkaWVudD4KICA8cmVjdCB4PSIwIiB5PSIwIiB3aWR0aD0iMSIgaGVpZ2h0PSIxIiBmaWxsPSJ1cmwoI2dyYWQtdWNnZy1nZW5lcmF0ZWQpIiAvPgo8L3N2Zz4=);
  background: -moz-linear-gradient(top,  rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%); /* FF3.6+ */
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(0,0,0,0)), color-stop(100%,rgba(0,0,0,0.7))); /* Chrome,Safari4+ */
  background: -webkit-linear-gradient(top,  rgba(0,0,0,0) 0%,rgba(0,0,0,0.7) 100%); /* Chrome10+,Safari5.1+ */
  background: -o-linear-gradient(top,  rgba(0,0,0,0) 0%,rgba(0,0,0,0.7) 100%); /* Opera 11.10+ */
  background: -ms-linear-gradient(top,  rgba(0,0,0,0) 0%,rgba(0,0,0,0.7) 100%); /* IE10+ */
  background: linear-gradient(to bottom,  rgba(0,0,0,0) 0%,rgba(0,0,0,0.7) 100%); /* W3C */
  filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00000000', endColorstr='#b3000000',GradientType=0 ); /* IE6-8 */
}
div.event h3:hover
{
  background: rgba(0,0,0,0.7);
}
div.event > .content
{
  height: 100%;
  overflow: hidden;
  background: url(img/partner.png) no-repeat;
}
div.event > .content > img
{
  height: 100%;
}
</style>
<link href="css/datepicker.css" rel="stylesheet">
<script src="js/bootstrap-datepicker.js"></script>
<script>
var EventList = function(element)
{
  var me = this;
  me.element = {};
  me.element.root = $(element);
  me.element.list = me.element.root.find("#event_list");
  me.data = {};

  me.setData = function(data)
  {
  	me.data = [];
  	for (var i = 0; i < data.length; ++i)
  		me.data.push(new Event(data[i]));
    me.refresh();
  };

  me.refresh = function()
  {
    var addEvent = function(event)
    {
      var $event = me.element.list.find("div.event.template").clone();
      $event.removeClass("template");
      $event.attr("data-src", event.id);
      $event.find("[data-ref='date']").text(event.date);
      $event.find("[data-ref='topic']").text(event.topic);

      $event.find("[data-link='edit']").attr("href", "event.php?id=" + event.id + location.search.replace("?","&"));
      me.element.list.append($event);
    };

    me.element.list.find("div.event:not(.template)").remove();

    me.data.sort(function(a, b)
    {
      return new Date(a.date) - new Date(b.date);
    });
    for (var i = 0; i < me.data.length; ++i)
    {
      addEvent(me.data[i]);
    }
  };
};

$(document).ready(function()
{
	var event_list = new EventList($("#page_content"));
	event_list.setData(_r.data);
});
</script>
        <!-- begin content -->
        <div class="span9" id="page_content">
          <h1>例會/活動回顧</h1>
          <div id="event_list">
            <div class="event template">
              <div class="content" data-ref="photos">
              </div>
              <div class="date" data-ref="date"></div>
              <a href="#" data-link="edit"><h3 data-ref="topic"></h3></a>
            </div>
          </div>

          <p class="pull-right" data-visible="owner">
            <a id="create_meeting" class="btn btn-primary" href="event.php" data-link="add">新增</a>
          </p>
          <p class="clearfix"></p>

<? $builder->outputPageInfo(); ?>

        </div><!--/.span9-->
        <!-- end content -->

      </div><!--/.row-fluid-->

<? $builder->outputFooter(); ?>

    </div><!--/.container-fluid-->
  </body>
</html>

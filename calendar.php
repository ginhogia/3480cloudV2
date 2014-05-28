<?
require_once("mod/session.php");
require_once("mod/page-builder.php");
$session = new Session();
$session->setPageId(PAGE_ID_CALENDAR);
$session->checkPermission();
$builder = new PageBuilder($session);
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

        <!-- begin content -->
        <div class="span9" id="page_content">
          <h1>行事曆</h1>
          <iframe style="border-width: 0;" src="https://www.google.com/calendar/b/0/embed?showTitle=0&amp;height=500&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src=6ibcp5jc7jaf5uneonsjbat4lk%40group.calendar.google.com&amp;color=%23691426&amp;ctz=Asia%2FTaipei" frameborder="0" scrolling="yes" width="100%" height="500"></iframe>
        </div><!--/.span9-->
        <!-- end content -->

      </div><!--/.row-fluid-->

<? $builder->outputFooter(); ?>

    </div><!--/.container-fluid-->
  </body>
</html>

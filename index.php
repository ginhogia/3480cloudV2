<?
require_once("mod/session.php");
require_once("mod/page-builder.php");
$session = new Session();
$session->setPageId(PAGE_ID_INDEX);
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
          <div class="hero-unit">
            <h1>國際扶輪3480地區扶輪青年服務團 雲端服務中心</h1>
            <p>地區資訊服務平台，各項功能陸續上線測試，如遇系統操作問題，請協助回報，讓我們把系統做得更好。</p>
            <!--<p><a href="#" class="btn btn-primary btn-large">Learn more &raquo;</a></p>-->
          </div>
          <div class="row-fluid">
            <div class="span6">
              <h2>2013-14年度11月份團秘會</h2>
              <p>會議日期：2013/11/04</p>
              <p>會議時間：18:30用餐、聯誼；19:30開始；21:00結束</p>
              <p>會議地點：雙連教會8樓804室（台北市中山北路二段111號8樓）</p>
              <!--<p><a class="btn" href="#">View details &raquo;</a></p>-->
            </div>
            <div class="span6">
              <h2>2013-14年度12月份團秘會</h2>
              <p>會議日期：2013/12/02</p>
              <p>會議時間：18:30用餐、聯誼；19:30開始；21:00結束</p>
              <p>會議地點：雙連教會8樓804室（台北市中山北路二段111號8樓）</p>
              <!--<p><a class="btn" href="#">View details &raquo;</a></p>-->
            </div>
          </div><!--/row-->
          <div class="row-fluid">
          </div><!--/row-->
        </div><!--/.span9-->
        <!-- end content -->

      </div><!--/.row-fluid-->

<? $builder->outputFooter(); ?>

    </div><!--/.container-fluid-->
  </body>
</html>

<?php
error_reporting(0);
if($_GET['ac']=="clear"){
    \Workerman\Protocols\Http::setcookie("RMTPLAYER_id","",time()-3600);
    \Workerman\Protocols\Http::setcookie("password","",time()-3600);
    header("location:./");
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Remote Player</title>

    <!-- MZUI CSS file -->
    <link href="http://api.xtlsoft.top/frontend/mzui/css/mzui.min.css" rel="stylesheet">
  </head>
  <body>
      <div class="heading" style="background-color:#03B8CF;">
        <nav class="nav" style="display:none;">
           <a><i class="icon-user"></i></a>
        </nav>
        <div class="title text-center" style="color:white;font-size:1rem;">Remote Player</div>
        <nav class="nav" style="display:none;">
           <a><i class="icon-search"></i></a>
        </nav>
      </div>
<div class="cell box">

      <div class="tile flex-center flex flex-column">

        <form method="get">

          <div class="control box danger form-message hide-empty" style="display: none;"></div>

          <div class="control has-label-left fluid">

            <input autofocus="" autocomplete="off" value="<?=$_REQUEST['id']?$_REQUEST['id']:@$_COOKIE['RMTPLAYER_id']?>" id="account" name="id" type="text" class="input" placeholder="请输入设备ID">

            <label for="account" title="设备id"><i class="icon icon-user"></i></label>

            <p class="help-text"></p>

          </div>

        <?php if(!@$_COOKIE['RMTPLAYER_id']): ?>
          <div class="control has-label-left fluid">

            <input autofocus="" autocomplete="off" id="password" name="password" type="password" class="input" placeholder="请输入密码">

            <label for="password" title="密码"><i class="icon icon-user"></i></label>

            <p class="help-text"></p>

          </div>
        <?php else: ?>
          
            <input autofocus="" autocomplete="off" id="password" name="password" type="hidden" placeholder="请输入密码" value="<?=$_COOKIE['RMTPLAYER_password']?>">

        <?php endif; ?>

          <div class="control has-label-left fluid">

            <textarea id="message" name="message" class="input"></textarea>

            <label for="password" title="文字消息/音乐地址"><i class="icon icon-comments"></i></label>

            <p class="help-text">如果是地址，要以<code>URL=</code>开头。 例子：<code>一个测试消息</code> 或者 <code>URL=http://test.com/music.mp3</code></p>

          </div>

          <div class="control">

            <button type="submit" class="btn primary fluid">发送</button>

          </div>

          <div class="control">

            <div class="checkbox">

              <input type="checkbox" name="keepLogin" value="on">

              <label for="keepLogin">记住状态</label>

            </div>

          </div>

          <div class="control">

            <div class="checkbox">

              <input type="checkbox" id="autoplay" name="autoplay" value="on" checked>

              <label for="autoplay">自动播放</label>

            </div>

          </div>

        </form>

      </div>

    </div>
    
    <br />
    <p align="center">Powered by xtlsoft</p>
    <p align="center"><a href="./?ac=clear">清除痕迹</a></p>
    <!-- MZUI JS file -->
    <script src="http://api.xtlsoft.top/frontend/mzui/js/mzui.min.js"></script>
  </body>
</html>

<?php
	if($_GET['message']){
        
        $rslt = file_get_contents(\RemotePlayer\WebInterface::$config['listen']['RegisterServer'] . "/?action=auth&device="
            . urlencode($_REQUEST['id']) . "&password="
            . urlencode(hash("sha256", $_REQUEST['password']))
        );
        if(json_decode($rslt, 1)['status'] !== "success"){
            echo("<script>alert('密码错误！');</script>");
            
        }else{

        $voice = $_REQUEST['message'];

        if(substr($voice, 0, 4) == "URL="){
            $voice = substr($voice, 4);
        }else{
            $voice = "http://120.24.87.124/cgi-bin/ekho2.pl?cmd=SPEAK&voice=BaiduMandarinFemale&speedDelta=0&pitchDelta=0&volumeDelta=0&text=" . urlencode($voice);
        }

        $rslt = file_get_contents(\RemotePlayer\WebInterface::$config['listen']['RegisterServer'] . "/?action=sendVoice&device="
            . urlencode($_REQUEST['id']) . "&password="
            . urlencode(hash("sha256", $_REQUEST['password'])) . "&voice="
            . urlencode($voice) . "&autoplay=" . 
            (isset($_REQUEST['autoplay']) ? 1 : 0)
        );

	    if($_GET['keepLogin']){
            \Workerman\Protocols\Http::setcookie("RMTPLAYER_id",$_REQUEST['id']);
            \Workerman\Protocols\Http::setcookie("RMTPLAYER_password", $_REQUEST['password']);
        }
        
        }
	}

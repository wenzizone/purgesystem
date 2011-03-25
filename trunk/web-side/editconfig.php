<?php

/*
 File Name: purge_system
 Description: purge是一个分布式清除squid,nginx缓存的应用，使用php编写。
 Version: 2.0
 Author: 蚊子
 Author URI: http://www.wenzizone.cn
 Disclaimer: Use at your own risk. No warranty expressed or implied is provided.

 ---
 Copyright 2010  蚊子  (email : wenzizone@gmail.com)
 ---

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 ---
 Changelog
 ---

 See the readme.txt.

 */


# 将配置文件读入数组
$aFContents = file("config.ini");

# 修改配置文件
function fh_write_config($strEditContent) {
    $rsFH = fopen("config.ini","wb");
    if ($rsFH) {
        $bRes = fwrite($rsFH,$strEditContent);
        if ($bRes === false) {
            return(false);
        } else {
            return(true);
        }
    } else {
        return(false);
    }
}

if($_POST['textfield']) {
    # 拿到文本框里的修改内容
    $strEditContent = $_POST['textfield'];

    $bRes = fh_write_config($strEditContent);
    if ($bRes) {
        header("location:" . basename(__FILE__));
    }
}
?>

<!--  页面显示部分  -->
<?php require('./header.php'); ?>
<div class="nav"><a href="purge_cache.php">清除缓存</a><span>|</span>编辑cache服务器组列表
</div>
<div class="uploadBox">
<div class="content">
<form action="" method="post" enctype="multipart/form-data" name="form1"
	id="form1">
<table class="upTable" border="0" align="center" cellpadding="0"
	cellspacing="0">
	<tr>
		<td width="100%">在下面的编辑框内修改squid服务器的配置文件</td>
	</tr>
	<tr>
		<td width="100%"><textarea name="textfield" cols="100"
			rows="<?php echo count($aFContents)+1;?>" id="textfield"><?php foreach($aFContents as $k=>$v) {
			    echo $v;
			}?></textarea></td>
	</tr>
	<tr>
		<td width="100%" align="left"><input type="submit" name="submit"
			id="submit" value="修改" /></td>
	</tr>
</table>
</form>
</div>
</div>
			<?php require('./footer.php'); ?>

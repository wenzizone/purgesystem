<?php
/*
 File Name: Purge Cache
 Description: Purge Cache是一个可以通过web方式清除squid,nginx缓存的应用，使用php编写。
 Version: 1.2.3
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



# 从配置文件获取squid服务器列表

$squid_host = parse_ini_file('config.ini',true);

# 清除缓存
function purge_file($squid_host,$squid_port,$file_url) {
    $result = array();
    for ($i = 0; $i<=1; $i++) {
        $fp = fsockopen($squid_host,$squid_port,$errno,$errstr,5);
        if (!$fp) {
            return(FALSE);
            //echo "$errstr ($errno)<br />\n";
        } else {
            $header = "PURGE $file_url HTTP/1.0\r\n";
            $header .= "Connection: Close\r\n\r\n";
            fwrite($fp, $header);
            $result[$i] = fgets($fp) . "<br/>";
            fclose($fp);
        }
    }
    return($result);
}

# 输出清除缓存的结果
function display_message($purge_result,$squid_host,$url) {
    if ($purge_result) {
        $result_1 = explode(" ", $purge_result['0']);
        $result_2 = explode(" ", $purge_result['1']);
        if ($result_1['1'] == 404) {
            $display_message = "您提交的地址：$url 已经在cache服务器：" . $squid_host . "上刷新！";
        } elseif ($result_1['1'] == 200 && $result_2['1'] == 404) {
            $display_message = "您提交的地址：$url 已经在cache服务器：" . $squid_host . "上刷新！";
        } elseif ($result_1['1'] == 403) {
            $display_message = "在cache服务器：" . $squid_host . "上的请求被拒绝，可能是这台机器不被授权刷新缓存！";
        }
    } else {
        $display_message = "连接不上cache服务器：" . $squid_host . "，请重新尝试！";
    }
    //$result = array_merge($dis_mes,$display_message);
    return($display_message);
}

# main ;
$display_message = array();
if ($_POST['url']) {
    $url = trim($_POST['url']);
    $url_array = preg_split("/[\s]+/",$url); //将url存入数组
    $s_list = $_POST['squid_list']; //获得squid的组
    foreach($url_array as $u) { //获得每一个url地址
        //foreach ($squid_host as $v) {
        foreach ($squid_host[$s_list] as $v) { //获得每一台squid的地址
            $squid_host_array = explode(":",$v);
            $SQUID_HOST = $squid_host_array['0'];
            $SQUID_PORT = $squid_host_array['1'];
            $res = purge_file($SQUID_HOST,$SQUID_PORT,$u);
            $display_message[$SQUID_HOST][$u] = display_message($res,$SQUID_HOST,$u);
        }
    }
}
?>

<!--  页面显示部分  -->
<?php require('./header.php'); ?>
<div class="nav">清除缓存 <span>|</span> <a href="editconfig.php">编辑cache服务器组列表</a>
</div>

<div class="uploadBox">
<div class="content">
<form action="" method="post" enctype="multipart/form-data" name="form1"
	id="form1">
<table class="upTable" border="0" align="center" cellpadding="0"
	cellspacing="0">
	<tr>
		<td width="100%" align="left" colspan="2">在下面输入要清除的url地址，如果有多个地址，每个url一行</td>
	</tr>
	<tr>
		<td width="80%" align="right">选择要清除的cache组：</td>
		<td width="20%" align="left"><select name="squid_list" size="1"
			id="squid_list">
			<?php foreach($squid_host as $key=>$value) {?>
			<option value="<?php echo "$key";?>"><?php echo "$key";?></option>
			<?php }?>
		</select></td>
	</tr>
	<tr>
		<td colspan="2"><textarea name="url" cols="100" rows="10" id="url"></textarea></td>
	</tr>
	<tr>
		<td colspan="2"><input type="submit" name="submit" id="submit"
			value="提交" /></td>
	</tr>
</table>
</form>
<table>
	<tr>
		<td align="left"><?php foreach($display_message as $k=>$v) {
		    foreach($v as $k1=>$v1){
		        print "$v1<br/>";
		    }
		}
		?></td>
	</tr>
</table>
</div>
</div>
</div>
		<?php require('./footer.php'); ?>

<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>29- วแํๆใ วแสวำฺ ๆวแฺิัํไ - ิๅั สๆส</p>";
$ar_text.= "
<p $arstaci>สะ฿วั วแใฺฬาษ วแสํ ีไฺๅว วแัศ ใฺ วแÞฯํำ รหไวำํๆำ วแัำๆแํ </p>
";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
echo $ar_text2;
?>

<?php
defined('_ALONE') or die('Restricted access');
$ar_text="
<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>17- «бнжг «б”«»Џ Џ‘— - ‘е— √»н»</p>";
$ar_text.= "
<p $arstaci>«” ‘е«ѕ «бёѕн”… √жЁнгн… </p>
<p $arstaci>«” ‘е«ѕ «бёѕн” нд  яб« жг—Ћ« гд √”д« </p>
";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

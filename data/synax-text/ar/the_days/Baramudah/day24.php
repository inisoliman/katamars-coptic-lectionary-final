<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>24- Çáíæã ÇáÑÇÈÚ æÇáÚÔÑíä - ÔåÑ ÈÑãæÏÉ</p>";

$ar_text.= "
<p $arstaci>ÇÓÊÔåÇÏ ÓäÇ ÇáÌäÏì ÑÝíÞ ÅíÓíÐíÑæÓ </p>
<p $arstaci>äíÇÍÉ ÇáÞÏíÓ ÇáÈÇÈÇ ÓÇäæÊíæÓ ÇáÃæá Çá55 </p>
";

$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

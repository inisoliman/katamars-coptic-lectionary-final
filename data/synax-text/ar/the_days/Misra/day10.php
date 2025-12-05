<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>10- Çáíæã ÇáÚÇÔÑ - ÔåÑ ãÓÑì</p>";
$ar_text.= "
<p $arstaci>ÇÓÊÔåÇÏ ÇáÞÏíÓ ÈíÎíÈÓ </p>
<p $arstaci>ÇÓÊÔåÇÏ ÇáÞÏíÓ ãØÑÇ æÈíÎÈÓ </p>
";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

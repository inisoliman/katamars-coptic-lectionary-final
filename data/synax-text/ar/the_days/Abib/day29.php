<?php
defined('_ALONE') or die('Restricted access');
$ar_text="
<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>29- Çáíæã ÇáÊÇÓÚ æÇáÚÔÑíä - ÔåÑ ÃÈíÈ</p>";
$ar_text.= "
<p $arstaci>ÇÓÊÔåÇÏ ÇáÞÏíÓ æÑÔäæÝíæÓ </p>
<p $arstaci>ÊÐßÇÑ äÞá ÃÚÖÇÁ ÇáÞÏíÓ ÇäÏÑÇæÓ ÇáÑÓæá </p>
";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

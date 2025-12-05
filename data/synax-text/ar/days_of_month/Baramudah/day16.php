<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>16- Çáíæã ÇáÓÇÏÓ ÚÔÑ - ÔåÑ ÈÑãæÏÉ</p>";

$ar_text.= "
<p $arstaci>ÇÓÊÔåÇÏ ÃäÊíÈÇÓ ÊáãíÐ íæÍäÇ ÇáÑÓæá ÃÓÞÝ ãÏíäÉ ÈÑÛÇãÓ </p>
";

$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

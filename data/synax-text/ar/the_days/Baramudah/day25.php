<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>25- Çבםזד ÇבÎÇדÓ זÇבÚÔÑםה - ÔוÑ ÈÑדזÏÉ</p>";

$ar_text.= "
<p $arstaci>ÇÓÊÔוÇÏ ÇבÞÏםÓ ÓÇÑÉ זזÇבÏםוÇ </p>
<p $arstaci>ÊÐ‗ÇÑ ÇבÞÏםÓ ÈÈהזÏו ÇבדÊזÍÏ </p>
";

$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

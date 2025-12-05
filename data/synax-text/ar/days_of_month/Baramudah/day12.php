<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>12- «бнжг «бЋ«дм Џ‘— - ‘е— »—гжѕ…</p>";

$ar_text.= "
<p $arstaci>дн«Ќ… «бя”дѕ—ж” √”ёЁ «ж—‘бнг </p>
<p $arstaci>«б –я«— «б‘е—н б—∆н” «бгб«∆я… «бћбнб гнќ«∆нб </p>
<p $arstaci> –я«— «д»« «дЎжднж” «”ёЁ Ўгже </p>
";

$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

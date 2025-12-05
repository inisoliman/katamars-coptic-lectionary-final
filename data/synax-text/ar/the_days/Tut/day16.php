<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p style=\"charset:utf-8;\" />";
$ar_text.="<p $arstay>16- «бнжг «б”«ѕ” Џ‘— - ‘е—  ж </p>";
$ar_text.= "
<p $arstaci> я—н” ядн”… «бён«г… »√ж—‘бнг Џбм нѕ «бёѕн” √Ћд«”нж” «б—”жбн 
”д… 42‘ </p>
<p $arstaci>дёб ћ”ѕ «бёѕн” нжЌд« Ёг «б–е» </p>
";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
?>

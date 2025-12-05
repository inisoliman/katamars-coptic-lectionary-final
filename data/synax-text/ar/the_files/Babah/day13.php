<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p $arstay>13- «бнжг «бЋ«бЋ Џ‘— - ‘е— »«»е</p>";
$ar_text.= "
<p $text_ar_1>дн«Ќ… «бёѕн” “я—н« «б—«е» »д ё«—»ж” </p>

<p $text_ar_2> –я«— дн«Ќ… «бёѕн” “я—н« «б—«е» »д ё«—»ж” . ’бж« е  яжд гЏд« 
жб—»д« «бгћѕ ѕ«∆г« ≈»ѕн« «гнд</p>

";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
echo $ar_text2;
?>

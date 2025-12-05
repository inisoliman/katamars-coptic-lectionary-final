<?php
defined('_ALONE') or die('Restricted access');
$ar_text="
<p $text_ar_1 >«” ‘е«ѕ «бёѕн” ”ндж”нж” ( 4 »№ƒжд…)</p>
<p $text_ar_2>Ён гЋб е–« «бнжг «” ‘е«ѕ «бёѕн” ”ндж”нж”. ’б« е  яжд гЏд« ж 
б—»д« «бгћѕ ѕ«∆г« «»ѕн« «гнд .</p>


";
$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
echo $ar_text2;
?>




<?php
defined('_ALONE') or die('Restricted access');
$ar_text="<p $arstay>08- «бнжг «бЋ«гд - ‘е— √»н»</p>";

$ar_text.= "

<p $text_ar_1>дн«Ќ… «бёѕн” «бЏўнг «б«д»« »н‘жм яжя» «б»—н…</p>

";


if(file_exists("components/$option/icons/abib8-1.jpg"))
{
$ar_text.= "<p $text_ar_2m >";
echo "<img src='components/$option/icons/abib8-1.jpg' style='float:right;margin:10px; max-width:100px;' alt='icon' /><br />"; 
} 
else 
{
$ar_text.= "<p $text_ar_2>NO";
}

$ar_text.= "
Ён гЋб е–« «бнжг дн«Ќ… «бёѕн” «бЏўнг «б«д»« »н‘жм яжя» 
«б»—н…. ’б« е  яжд гЏд« ж б—»д« «бгћѕ ѕ«∆г« «»ѕн« «гнд .</p>

<p $text_ar_1>«” ‘е«ѕ «бёѕн” «»н—ж ж«ѕжг ж»б«д« </p>

<p $text_ar_2>Ён гЋб е–« «бнжг «” ‘е«ѕ «бёѕн” «»н—ж ж«ѕжг ж»б«д«. ’б«… 
«бћгнЏ  яжд гЏд« ж б—»д« «бгћѕ ѕ«∆г« «»ѕн« «гнд .</p>

<p $text_ar_1>дн«Ќ… «бёѕн” я«—«” ‘ёнё Ћ«ƒѕ”нж” «бгбя </p>

<p $text_ar_2>Ён гЋб е–« «бнжг дн«Ќ… «бёѕн” я«—«” ‘ёнё Ћ«ƒѕ”нж” «бгбя. 
’б« е  яжд гЏд« ж б—»д« «бгћѕ ѕ«∆г« «»ѕн« «гнд 
.</p>
";

$ar_text2=iconv("windows-1256", "utf-8", $ar_text );
echo $ar_text2;
?>

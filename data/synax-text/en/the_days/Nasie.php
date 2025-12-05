<?php
echo "This One??<br />";
include("the_files/synax/syn_globals.php");
include("style/css.php");

$go_main_b="index.php?view=the_days&amp;iday=$iday&amp;imonth=$imonth&amp;iyear=$iyear&amp;rdr=Nasie";
?>
 <table>
<tr>
<td <?php echo $td_style1;?>>
<b>Select a day below :</b></td>

<td <?php echo $text_3; ?>>
<b>&nbsp;&nbsp;  Synopsis</b></td>
</tr>
</table>
<?php echo $hr; ?>
<br />
<table>
 <tr>
 <td <?php echo $td_style1; ?>><a <?php echo $a_link_1;   ?>href="<?php echo $go_main_b; ?>/day1.php"><?php echo "$Nasie" ?>
 1</a> </td>
 
 <td <?php echo $text_3; ?>>Departure of St.Eutychus
 <br />Martyrdom of St.Pishay (Abshai), the Antiochian</td>
 </tr>
 
 <tr>
 <td <?php echo $td_style1; ?>><a <?php echo $a_link_1;   ?>href="<?php echo $go_main_b; ?>/day2.php"><?php echo $Nasie; ?>
 2</a> </td>
 
 <td <?php echo $text_3; ?>>Departure of St.Titus, the Apostle</td>
 </tr>
 
 <tr>
 <td <?php echo $td_style1; ?>><a <?php echo $a_link_1;   ?>href="<?php echo $go_main_b; ?>/day3.php"><?php echo $Nasie; ?>
 3</a> </td>
 
 <td <?php echo $text_3; ?>>Commemoration of the Angel Raphael, the Archangel
 <br />Martyrdom of St.Andrianus
 <br />Departure of St.Yoannis XIV, the 96th Pope of Alexandria</td>
 </tr>
 
 <tr>
 <td <?php echo $td_style1; ?>><a <?php echo $a_link_1;   ?>href="<?php echo $go_main_b; ?>/day4.php"><?php echo $Nasie; ?>
 4</a> </td>
 
 <td <?php echo $text_3; ?>>Departure of St.Leparius, Bishop of Rome
 <br />Departure of St.Poimen, the Hermit</td>
 </tr>
 
 <tr>
 <td <?php echo $td_style1; ?>><a <?php echo $a_link_1;   ?>href="<?php echo $go_main_b; ?>/day5.php"><?php echo $Nasie; ?>
 5</a> </td>
 
 <td <?php echo $text_3; ?>>Departure of St.James, Bishop of Misre
 <br />Departure of the Righteous Amos, the Prophet
 <br />Departure of St.Barsoma, the &quot;Naked&quot;
 <br />Departure of St.Yoannis XV, the 99th Pope of Alexandria</td>
 </tr>
 
 <tr>
 <td <?php echo $td_style1; ?>><a <?php echo $a_link_1;   ?>href="<?php echo $go_main_b; ?>/day6.php"><?php echo $Nasie; ?>
 6</a> </td>
 
 <td <?php echo $text_3; ?>>A Thanksgiving to God</td>
 </tr>
 </table>

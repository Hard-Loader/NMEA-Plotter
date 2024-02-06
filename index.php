<?php
set_time_limit(300);

/*
$GPGSV,<1>,<2>,<3>,<4>,<5>,<6>,<7>,<8>,<9>,<10>,<11>,<12>,<13>,<14>,<15>,<16>,<17>,<18>,
             <19>*hh<CR><LF>

Fields
<1>    Total number of GSV sentences (1 to 3 for internal GPS if WeatherStation)
<2>    Sentence number (1, 2, or 3)
<3>    Total number of satellites in view
<4>    Satellite ID number, 1st SV
<5>    Elevation degrees, 0 to 90, to the nearest degree, 1st SV
<6>    Azimuth, degrees True, to the nearest degree, 1st SV
<7>    SNR (C/No) 00-99 dB-Hz, 1st SV (null field if satellite not tracked)
<8>    Satellite ID number, 2nd SV
<9>    Elevation degrees, 0 to 90, to the nearest degree, 2nd SV
<10>   Azimuth, degrees True, to the nearest degree, 2nd SV
<11>   SNR (C/No) 00-99 dB-Hz, 2nd SV (null field if satellite not tracked)
<12>   Satellite ID number, 3rd SV
<13>   Elevation degrees, 0 to 90, to the nearest degree, 3rd SV
<14>   Azimuth, degrees True, to the nearest degree, 3rd SV
<15>   SNR (C/No) 00-99 dB-Hz, 3rd SV (null field if satellite not tracked)
<16>    4th SV
<17>   Elevation degrees, 0 to 90, to the nearest degree, 4th SV
<18>   Azimuth, degrees True, to the nearest degree, 4th SV
<19>   SNR (C/No) 00-99 dB-Hz, 4th SV (null field if satellite not tracked)
*/

$lookup = array();
$lookup['02']="USA-180";
$lookup['03']="USA-258";
$lookup['04']="USA-289 Vespucci";
$lookup['05']="USA-206";
$lookup['06']="USA-251";
$lookup['07']="USA-201";
$lookup['08']="USA-262";
$lookup['09']="USA-256";
$lookup['10']="USA-265";
$lookup['11']="USA-319 Neil Armstrong";
$lookup['12']="USA-192";
$lookup['13']="USA-132";
$lookup['14']="USA-309 Sacagawea";
$lookup['15']="USA-196";
$lookup['16']="USA-166";
$lookup['17']="USA-183";
$lookup['18']="USA-293 Magellan";
$lookup['19']="USA-177";
$lookup['20']="USA-150";
$lookup['21']="USA-168";
$lookup['22']="USA-151";
$lookup['23']="USA-304 Matthew Henson";
$lookup['24']="USA-239";
$lookup['25']="USA-213";
$lookup['26']="USA-260";
$lookup['27']="USA-242";
$lookup['28']="USA-343 Amelia Earhart";
$lookup['29']="USA-199";
$lookup['30']="USA-248";
$lookup['31']="USA-190";
$lookup['32']="USA-266";




$data = array();
$handle = fopen("./20240206.nmea", "r");
//$handle = fopen("./nmea.log", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $line = explode(',',$line);
        if ($line[0]=='$GPGSV'){
            if (isset($line[7]) && ($line[7]!=''))   $data[] = array($line[5],$line[6],$line[7],$line[4]);
            if (isset($line[11]) && ($line[11]!='')) $data[] = array($line[9],$line[10],$line[11],$line[8]);
            if (isset($line[15]) && ($line[15]!='')) $data[] = array($line[13],$line[14],$line[15],$line[12]);
            if (isset($line[19]) && ($line[19]!='')) $data[] = array($line[17],$line[18],$line[19],$line[16]);
        }
    }
    fclose($handle);
}else{
    echo "failed to open file";
}

$satellites = array();
$used_position  = array();
$output = array();
foreach($data as $point){
    $r = cos(deg2rad($point[0]));
    $x=$r*cos(deg2rad($point[1]));
    $x=100*($x+1)/2; // percent
    $y=$r*sin(deg2rad($point[1]));
    $y=100*($y+1)/2; //percent
    $z= (50 + $point[2])/100; //opacity
    if(!in_array($point[3],$satellites)){
        $satellites[] = $point[3];
    }
    $current_position = number_format($x, 3, '.', '').number_format($y, 3, '.', '');
    if (!in_array($current_position,$used_position)){
        //echo $current_position."<br>";
        $used_position[] = $current_position;
        $output[] = array($x,$y,$z,$point[3]);
    }
}

$satellites = array_flip($satellites);
//print_r($data);
//print_r($output);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NMEA Plotter</title>
    <style>
        .plot {position:absolute; width:5px; height:5px;   background:#000; border:0px; border-radius:2.5px; }
        .col0 {background:#ff0000}
        .col1 {background:#00ff00}
        .col2 {background:#0000ff}
        .col3 {background:#ffff00}
        .col4 {background:#ff00ff}
        .col5 {background:#00ffff}
        .col6 {background:#99ff00}
        .col7 {background:#0099ff}
        .col8 {background:#ff0099}
        .col9 {background:#ff9933}
        .col10 {background:#33ff99}
        .col11 {background:#9933ff}
        .col12 {background:#ff3399}
        .col13 {background:#99ff33}
        .col14 {background:#3399ff}
        .col15 {background:#3333ff}
    </style>
  </head>
  <body>
<h1>NMEA Plotter</h1>
<div style="position:absolute; border:1px solid #333; margin:auto; width:900px; height:900px">
<?php
$count=0;
foreach($output as $plot){
 echo "<div class='plot col";
 echo $satellites[$plot[3]];
 echo " ' style='";
 echo "margin-top: ".$plot[0]."%; ";
 echo "margin-left: ".$plot[1]."%; ";
 echo "opacity: ".$plot[2].";'>";
 echo "<p>".$lookup[$plot[3]]."</p>";
 $lookup[$plot[3]]='';
 echo "</div>\n";
 if($count++ > 400000) break;
}
?>
<br style="margin-top:900px">
<p>Plotted <?=$count?> points from <?php echo count($satellites);?> satellites.</p>
<!--<pre><?php print_r($satellites)?></pre>-->
</div>

</body>
</html>

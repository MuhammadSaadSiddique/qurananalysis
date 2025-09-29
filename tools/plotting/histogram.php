<?php
/**
 * Histogram Plotting Tool
 *
 * This script generates a histogram chart using the Google Charts API. It is designed
 * to be loaded within an iframe to display a visualization of a given dataset.
 *
 * The script retrieves the data to be plotted from the `$_SESSION['PLOTTING_DATA']`
 * session variable. The calling script is responsible for setting this session data
 * before loading this page. The data is then formatted into a JavaScript array and
 * used by the Google Charts library to render the histogram.
 *
 * @package QuranAnalysis
 */
#   PLEASE DO NOT REMOVE OR CHANGE THIS COPYRIGHT BLOCK
#   ====================================================================
#
#    Quran Analysis (www.qurananalysis.com). Full Semantic Search and Intelligence System for the Quran.
#    Copyright (C) 2015  Karim Ouda
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#    You can use Quran Analysis code, framework or corpora in your website
#	 or application (commercial/non-commercial) provided that you link
#    back to www.qurananalysis.com and sufficient credits are given.
#
#  ====================================================================
session_start();
$dataArr = $_SESSION['PLOTTING_DATA'];

//print_r($dataArr);


?>
<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
    	  var data = new google.visualization.DataTable();
    	  data.addColumn('string', 'Key');
    	  data.addColumn('number', 'Value');
    	  data.addRows([
    	    <?php 
    	    foreach( $dataArr as $key=>$val ):
    	    ?>
			['<?=$key?>',<?=$val?>],
    	    <?php endforeach;?>

    	  ]);

        var options = {
          title: 'Data Dsitribution',
          legend: { position: 'none' },
          histogram: { bucketSize: 1 },
 
        
        };

        var chart = new google.visualization.Histogram(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
    <div id="chart_div" style="width: 900px; height: 500px;"></div>
  </body>
</html>
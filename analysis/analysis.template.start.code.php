 <?php
/**
 * Analysis Section Start Template
 *
 * This file provides the common starting HTML and PHP code for all pages
 * within the 'analysis' section of the website. It includes the main site header,
 * the header menu, and sets up a two-column layout. The left column is
 * populated with the analysis-specific navigation menu (`left-menu.php`),
 * and the right column is opened for the specific page's content to be inserted.
 *
 * This template is typically paired with `analysis.template.end.code.php` to
 * complete the page structure.
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
 
 $langParameter = "";
 if ( isset($_GET['lang']))
 {
 	$langParameter = "?lang=".$_GET['lang'];
 }

 ?>

			  	
     <?php 
		require("../header.php");
	 ?>
  		

 
  	<div id='options-area' class='oa-analysis'>
			  	<?php 
			  		include_once("../header.menu.php");
			  	?>

		  
   		
   				
   		
   					
	</div>
	
	<table id="analysis-main-table">
	<tr>
		<td id='analysis-left-menu-cell'>
			<?php 
				require("./left-menu.php");
	
			?>
		</td>
		<td id='analysis-component-cell'>
		
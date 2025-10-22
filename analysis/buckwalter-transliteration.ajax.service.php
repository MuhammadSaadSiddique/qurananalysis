<?php
/**
 * AJAX Service for Buckwalter Transliteration
 *
 * This script provides a real-time transliteration service between Arabic script
 * and Buckwalter transliteration. It is designed to be called via an AJAX request.
 *
 * The script expects a 'text' parameter in the GET request. It automatically
 * detects if the input text is Arabic.
 * - If the text is Arabic, it converts it to Buckwalter transliteration.
 * - If the text is not Arabic, it attempts to perform a reverse transliteration
 *   from Buckwalter to Arabic.
 *
 * The result is returned as an HTML snippet ready to be injected into the calling page.
 * It includes error handling for invalid Buckwalter input.
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
require_once("../global.settings.php");
include_once("../libs/core.lib.php");


$lang = "AR";


if ( isset($_GET['lang']) )
{
	$lang = $_GET['lang'];
}

loadModels("core",$lang);

$text = $_GET['text'];

$text = html_entity_decode($text);


if ( empty($text)) exit;



if ( isArabicString($text))
{

	$translation  = arabicToBuckwalter($text);
	?>

		<div class='buckwalter-trans-result-header'>Buckwalter Transliteration: <b><?= $translation?></b> </div>
	<?php 
}
else
{

	try 
	{
	
		$arabicTranslation  = buckwalterReverseTransliteration($text);
		?>
			<div class='buckwalter-trans-result-header'>Arabic Presentation: <b><?= $arabicTranslation?></b> </div>
	
		<?php 
	}
	catch(Exception $e)
	{
		showTechnicalError("Invalid Buckwalter segment [$text] !");	
		exit;
	}
}



?>
	




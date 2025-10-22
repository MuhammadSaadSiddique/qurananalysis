<?php
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
require_once(dirname(__FILE__)."/../global.settings.php");
require_once(dirname(__FILE__)."/core.lib.php");



$CUSTOM_TRANSLATION_TABLE_EN_AR = array();
$TABLE_LOADED = false;

/**
 * Loads the custom translation table from the file into a global array.
 *
 * The function reads the translation file, parses each line, and populates
 * the global `$CUSTOM_TRANSLATION_TABLE_EN_AR` array. It also sets the
 * global `$TABLE_LOADED` flag to true.
 *
 * @return array The loaded translation table.
 */
function loadTranslationTable()
{
	global $customTranslationTableFile;
	global $CUSTOM_TRANSLATION_TABLE_EN_AR,$TABLE_LOADED;
	
	
	
	$fileLinesArr = file($customTranslationTableFile,FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
	


	foreach ($fileLinesArr as $index => $line)
	{
		$lineArr = preg_split("/\|/", $line);
		
		$keyLang = trim(removeUnacceptedChars($lineArr[0]));
		$mainWord = trim(removeUnacceptedChars($lineArr[1]));
		$wordType = trim(removeUnacceptedChars($lineArr[2]));
		$translatedWord= trim(removeUnacceptedChars($lineArr[3]));

		if ( $keyLang=="EN")
		{
			$CUSTOM_TRANSLATION_TABLE_EN_AR[$mainWord]=array("EN_TEXT"=>$mainWord,"TYPE"=>$wordType,"AR_TEXT"=>$translatedWord,"KEY_LANG"=>$keyLang);
		}
		else 
		{
			$CUSTOM_TRANSLATION_TABLE_EN_AR[$mainWord]=array("EN_TEXT"=>$translatedWord,"TYPE"=>$wordType,"AR_TEXT"=>$mainWord,"KEY_LANG"=>$keyLang);
		}
	}
	
	$TABLE_LOADED = true;
	
	return $CUSTOM_TRANSLATION_TABLE_EN_AR;
}

/**
 * Checks if a given English or Arabic string is found in the translation table with a specific type.
 *
 * @param string $enArStr  The string to look for (can be English or Arabic).
 * @param string $wordType The type of the word (e.g., "CONCEPT").
 * @return bool True if the entry is found and valid, false otherwise.
 */
function isFoundInTranslationTable($enArStr,$wordType="CONCEPT")
{
	global $CUSTOM_TRANSLATION_TABLE_EN_AR;
	
	$enArStr = tranlstationCleanAndTrim(removeUnacceptedChars(($enArStr)));
	//preprint_r($CUSTOM_TRANSLATION_TABLE_EN_AR);exit;
		 	
	return  (isset($CUSTOM_TRANSLATION_TABLE_EN_AR[$enArStr]) 
			&& ($CUSTOM_TRANSLATION_TABLE_EN_AR[$enArStr]['TYPE']==$wordType) &&
		 	 !empty($CUSTOM_TRANSLATION_TABLE_EN_AR[$enArStr]['AR_TEXT']) && !empty($CUSTOM_TRANSLATION_TABLE_EN_AR[$enArStr]['EN_TEXT']) );
}

/**
 * Cleans and trims a string specifically for translation table lookups.
 *
 * @param string $str The input string.
 * @return string The cleaned and trimmed string.
 */
function tranlstationCleanAndTrim($str)
{
	//« spoils arabic words = 0xab
	$tobeReplacedStr = "\t\n\r\0\x0B ";
	return trim(trim(trim($str),$tobeReplacedStr));
}


/**
 * Checks if a given Arabic string is found in the translation table as a value with a specific type.
 *
 * @param string $arStr    The Arabic string to search for.
 * @param string $wordType The type of the word (e.g., "CONCEPT").
 * @return bool True if a matching entry is found, false otherwise.
 */
function isFoundInTranslationTableArabicKeyword($arStr,$wordType="CONCEPT")
{
	global $CUSTOM_TRANSLATION_TABLE_EN_AR;

	$translationKey = search2DArrayForValue($CUSTOM_TRANSLATION_TABLE_EN_AR, $arStr, array("KEY"=>"TYPE","VAL"=>$wordType) );
	
	/*
	echoN(count($CUSTOM_TRANSLATION_TABLE_EN_AR));
	echoN($arStr);
	preprint_r($translationKey);
	echoN($wordType);
	preprint_r($CUSTOM_TRANSLATION_TABLE_EN_AR[$translationKey]);
	*/
	
	if ($translationKey!==false)
	{
		return true;
	}
	
	return false;
}


/**
 * Retrieves a translation entry from the table by its English keyword.
 *
 * @param string $enStr The English keyword to look up.
 * @return array|false The translation entry array if found, false otherwise.
 */
function getTranlationEntryByEntryKeyword($enStr)
{
	global $CUSTOM_TRANSLATION_TABLE_EN_AR,$TABLE_LOADED;
	
	$enStr = trim($enStr);
	
	if ( !$TABLE_LOADED)
	{
		return false;
	}
	
	return $CUSTOM_TRANSLATION_TABLE_EN_AR[$enStr];
}
/**
 * Retrieves a translation entry from the table by its Arabic keyword.
 *
 * @param string $arStr The Arabic keyword to search for.
 * @return array|false The translation entry array if found, false otherwise.
 */
function getTranlationEntryByArabicEntryKeyword($arStr)
{
	global $CUSTOM_TRANSLATION_TABLE_EN_AR,$TABLE_LOADED;

	if ( !$TABLE_LOADED)
	{
		return false;
	}
	
	$translationKey = search2DArrayForValue($CUSTOM_TRANSLATION_TABLE_EN_AR, $arStr);
	

	return $CUSTOM_TRANSLATION_TABLE_EN_AR[$translationKey];
}



/**
 * Adds or updates an entry in the translation table.
 *
 * @param string $enStr     The English string.
 * @param string $entryType The type of the entry (e.g., "CONCEPT", "NONE").
 * @param string $arStr     The Arabic string.
 * @param string $keyLang   The primary language for the key ('EN' or 'AR').
 * @return bool True on successful addition.
 */
function addTranslationEntry($enStr, $entryType, $arStr,$keyLang="EN")
{
	global $CUSTOM_TRANSLATION_TABLE_EN_AR,$TABLE_LOADED;

	if ( !$TABLE_LOADED)
	{
		loadTranslationTable();
	}

	// ALLOW DUPOLICATE ENGLISH KEYS
	//if ( !isFoundInTranslationTable($enStr) )
	//{
	if ( empty($entryType))
	{
		$entryType="NONE";
	}
	else
	{
		$entryType = strtoupper($entryType);
			
	}
	
	if ( $keyLang=="EN")
	{

		$CUSTOM_TRANSLATION_TABLE_EN_AR[$enStr]=array("EN_TEXT"=>$enStr,"TYPE"=>$entryType,"AR_TEXT"=>$arStr,"KEY_LANG"=>$keyLang);
	}
	else
	{
		$CUSTOM_TRANSLATION_TABLE_EN_AR[$arStr]=array("EN_TEXT"=>$enStr,"TYPE"=>$entryType,"AR_TEXT"=>$arStr,"KEY_LANG"=>$keyLang);
	}
	
	return true;
	//}
	//else
	//{
	//	return false;
	//}



}

/**
 * Removes unaccepted characters from a string for the translation table.
 * Replaces parentheses with brackets and removes pipe, carriage return, and newline characters.
 *
 * @param string $text The input text.
 * @return string The cleaned text.
 */
function removeUnacceptedChars($text)
{
	$text = strtr($text, "(", "[");
	$text = strtr($text, ")", "]");
	
	return preg_replace("/\||\\r|\\n/", "", $text);
}

/**
 * Persists the current state of the translation table back to its file.
 *
 * @param array|null $CUSTOM_TRANSLATION_TABLE_EN_AR_PARAM Optional. The translation table to persist. If null, the global table is used.
 * @return bool False if the table is not loaded or empty, otherwise void.
 */
function persistTranslationTable($CUSTOM_TRANSLATION_TABLE_EN_AR_PARAM=null)
{
	global $customTranslationTableFile;
	global $CUSTOM_TRANSLATION_TABLE_EN_AR,$TABLE_LOADED;
	
	if (!empty($CUSTOM_TRANSLATION_TABLE_EN_AR_PARAM))
	{
		 $CUSTOM_TRANSLATION_TABLE_EN_AR = $CUSTOM_TRANSLATION_TABLE_EN_AR_PARAM;
	}
	
	
	if (!$TABLE_LOADED || empty($CUSTOM_TRANSLATION_TABLE_EN_AR)) return false;
	

	//preprint_r($CUSTOM_TRANSLATION_TABLE_EN_AR);exit;
	
	//clear file
	file_put_contents($customTranslationTableFile,"");
	
	foreach ($CUSTOM_TRANSLATION_TABLE_EN_AR as $enWord => $entryArr)
	{
		if ( empty($enWord) ) continue;
	
		$keyLang = tranlstationCleanAndTrim(removeUnacceptedChars($entryArr['KEY_LANG']));
		$enWord = tranlstationCleanAndTrim(removeUnacceptedChars($entryArr['EN_TEXT']));
		$wordType = tranlstationCleanAndTrim(removeUnacceptedChars($entryArr['TYPE']));
		$arTranslation = tranlstationCleanAndTrim(removeUnacceptedChars($entryArr['AR_TEXT']));
		
		if ( $keyLang=="EN")
		{
			$line = "$keyLang|$enWord|$wordType|$arTranslation\n";
		}
		else
		{
			$line = "$keyLang|$arTranslation|$wordType|$enWord\n";
		}
		
		
		file_put_contents($customTranslationTableFile, $line,FILE_APPEND);
	}
	
	
}


/**
 * Prints the entire translation table to the output.
 *
 * @return bool False if the table is not loaded, otherwise void.
 */
function printTranslationTable()
{

	global $CUSTOM_TRANSLATION_TABLE_EN_AR,$TABLE_LOADED;



	if (!$TABLE_LOADED) return false;


	echoN("TRANSLATION TABLE COUNT:".count($CUSTOM_TRANSLATION_TABLE_EN_AR));
	
	foreach ($CUSTOM_TRANSLATION_TABLE_EN_AR as $enWord => $entryArr)
	{
		$keyLang = trim(removeUnacceptedChars($entryArr['KEY_LANG']));
		$enWord = trim(removeUnacceptedChars($entryArr['EN_TEXT']));
		$wordType = trim(removeUnacceptedChars($entryArr['TYPE']));
		$arTranslation = trim(removeUnacceptedChars($entryArr['AR_TEXT']));

		$line = "$keyLang|$enWord|$wordType|$arTranslation\n";
		
		echoN($line);

	}


}

/*
 * TESTING 
 * $CUSTOM_TRANSLATION_TABLE_EN_AR = loadTranslationTable();

preprint_r($CUSTOM_TRANSLATION_TABLE_EN_AR);

addTranlationEntry("Person", "Concept", "شخص");

preprint_r($CUSTOM_TRANSLATION_TABLE_EN_AR);

persistTranslationTable();

$CUSTOM_TRANSLATION_TABLE_EN_AR = loadTranslationTable();

preprint_r($CUSTOM_TRANSLATION_TABLE_EN_AR);
 */

?>
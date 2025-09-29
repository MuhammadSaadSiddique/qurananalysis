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

/**
 * Converts an array of text strings into a graph object for visualization.
 *
 * This function processes an array of search result texts, tokenizes them into words,
 * and builds a graph structure with nodes representing unique words and links
 * representing sequential word occurrences. It excludes specified words and pause marks.
 * The graph size is capped to a maximum number of nodes.
 *
 * @param array $searchResultTextArr An array of strings, where each string is a text from a search result.
 * @param array $excludes            An associative array of words to exclude from the graph, where keys are the words.
 * @return array An associative array representing the graph, containing 'nodes', 'links', and a 'capped' flag.
 *               'nodes': An array of unique words with their properties (id, word, size, x, y).
 *               'links': An array of connections between nodes (source, target).
 *               'capped': An integer indicating the maximum number of nodes if the cap was reached, otherwise 0.
 */
function textToGraph($searchResultTextArr,$excludes)
{
	global $pauseMarksFile, $lang;
	
	$MAX_CAP = 300;
	
	$graphObj = array();
	$graphObj["capped"]=0;
	
	$graphNodes = array();
	$graphLinks = array();
	
	$pauseMarksArr = getPauseMarksArrByFile($pauseMarksFile);
	
	/** SHOULD BE ZERO BASED FOR D3 TO WORK - o.target.weight = NULL**/
	$nodeSerialNumber = 0;
	
	$lastWord = null;
	
	foreach($searchResultTextArr as $index => $text)
	{
	
		$textWordsArr = preg_split("/ /",$text);
		
		//echoN($text);
		
		foreach($textWordsArr as $word)
		{
			
			
			if ( $lang == "EN")
			{
				$word = cleanAndTrim($word);
				$word = strtolower($word);
			}
			
			//echoN($word);
			
			
			if ( $pauseMarksArr[$word]) continue;
			
			if ( $excludes[$word]==1) continue;
		

			
			if ( !isset($graphNodes[$word]) )
			{
				$graphNodes[$word]= array("id"=>$nodeSerialNumber++,"word"=>$word,"size"=>1,"x"=>rand(1,800),"y"=>rand(1,400));
			}
			else
			{
				$graphNodes[$word]["size"]=$graphNodes[$word]["size"]+1;
			}
			
			
			if ( $lastWord!=null )
			{
				$graphLinks[]=array("source"=>$graphNodes[$lastWord]["id"],"target"=>$graphNodes[$word]["id"]);
			}
			
			$lastWord = $word;
			
		}
		
		if ( count($graphNodes) > $MAX_CAP )
		{
			$graphObj["capped"]=$MAX_CAP;
			break;
		}
		
		
	}
	
	$graphObj["nodes"]=$graphNodes;
	$graphObj["links"]=$graphLinks;	
	
	//preprint_r($graphLinks);
	//preprint_r($graphNodes);
	
	return $graphObj;
}

?>
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
require_once(dirname(__FILE__)."/../libs/core.lib.php");
require_once(dirname(__FILE__)."/custom.translation.table.lib.php");

/**
 * Maps a Quranic Arabic Corpus (QAC) Part-of-Speech (POS) tag to a WordNet POS tag.
 *
 * @param string $qacPOS The QAC POS tag (e.g., "N", "V", "ADJ").
 * @return string The corresponding WordNet POS tag (e.g., "noun", "verb", "adj"). Defaults to "noun".
 */
function mapQACPoSToWordnetPoS($qacPOS)
{
	
	$trans = array("PN" => "noun", "N" => "noun", "V" => "verb", "ADJ" => "adj", "LOC" => "adv", "T" => "adv");
	
	// since concept extracted from relations may have "SUBJECT" or "OBJECT" POS
	if ( !isset($trans[$qacPOS]) )
	{
		return "noun";
	}
	return  strtr($qacPOS,$trans);
}

/**
 * Trims leading conjunctions (waw and fa) from an Arabic verb.
 * Note: The implementation comment suggests this might be a problematic function.
 *
 * @param string $verb The Arabic verb.
 * @return string The verb with leading conjunctions removed.
 */
function trimVerb($verb)
{
	//very bad idea, spoils everything
	return preg_replace("/^(وَ|فَ)/um", "", $verb);
}

/**
 * Checks if a given Part-of-Speech (POS) pattern constitutes a noun phrase.
 *
 * @param string $posPattern The POS pattern string.
 * @return bool True if the pattern is a recognized noun phrase, false otherwise.
 */
function isNounPhrase($posPattern)
{
	return ( $posPattern=="N" || $posPattern=="PN" || $posPattern=="DET N"
	);
	//REMOVED || $posPattern=="N PRON"  نصيبك
}


/**
 * Generates a structured array with empty values for a new concept's metadata.
 *
 * @return array An associative array with predefined keys for concept metadata.
 */
function generateEmptyConceptMetadata()
{
	return array("LEM"=>"","FREQ"=>0,
			"POS"=>"","SEG"=>array(),"SIMPLE_WORD"=>"",
			"ROOT"=>"","WEIGHT"=>"","AKA"=>array(),
			"TRANSLATION_EN"=>"","TRANSLITERATION_EN"=>"",
			"MEANING_AR"=>array(),"MEANING_EN"=>array(),
			"DBPEDIA_LINK"=>"","WIKIPEDIA_LINK"=>"", "IMAGES"=>"", "DESC_EN"=>array(), "DESC_AR"=>array());
}

/**
 * Searches an array of terms for an entry matching a given simple word.
 *
 * @param array  $finalTerms     The array of final terms to search through.
 * @param string $sentSimpleWord The simple word to find.
 * @return array|false The term array if found, false otherwise.
 */
function getTermArrBySimpleWord($finalTerms, $sentSimpleWord)
{


	foreach ($finalTerms as $lemaUthmani=>$termArr)
	{
			
		$mySimpleWord = $termArr['SIMPLE_WORD'];
			
		//echoN("$sentSimpleWord==$mySimpleWord");
			
		if ( $sentSimpleWord==$mySimpleWord)
		{
			return $termArr;
		}
			
	}

	return false;
}

/**
 * Adds a new concept to the final concepts array if it doesn't already exist.
 *
 * @param array  &$finalConceptsArr The array of final concepts, passed by reference.
 * @param string $newConceptName    The name of the new concept.
 * @param string $coneptType        The type of the concept (e.g., "T-BOX", "A-BOX").
 * @param string $exPhase           The extraction phase during which the concept was identified.
 * @param int    $freq              The frequency of the concept.
 * @param string $engTranslation    The English translation of the concept.
 * @return bool True if the concept was added, false if it already existed (though it might be updated).
 */
function addNewConcept(&$finalConceptsArr,$newConceptName,$coneptType,$exPhase,$freq,$engTranslation)
{





	if ( !isset($finalConceptsArr[$newConceptName]))
	{
		$conceptMetaDataArr = generateEmptyConceptMetadata();
		
		if ( !empty($engTranslation))
		{
			$conceptMetaDataArr['TRANSLATION_EN']=$engTranslation;
		}
		
		$newConceptName = trim($newConceptName);
		$engTranslation = trim($engTranslation);
		
		$finalConceptsArr[$newConceptName]=array("CONCEPT_TYPE"=>$coneptType,"EXTRACTION_PHASE"=>$exPhase,"FREQ"=>$freq,"EXTRA"=>$conceptMetaDataArr);
		
		return true;
	
	}
	else 
	{
		//
		// IT WAS MEANT TO BE T-BOX IF IT WAS NOT FOUND, SO IF IT IS FOUND SWITCH IT TO T-BOX SINCE IT IS A PARENT
		if ( $coneptType=="T-BOX")
		{
			// SHOULD SWITCH TO T-BOX SINCE IT IS A PARENT CLASS NOW - FOR OWL SERIALIZATION BUGS
			$finalConceptsArr[$newConceptName]['CONCEPT_TYPE']='T-BOX';
		}
		
		return false;
	}
		
	
	
}

/**
 * Prints a formatted string representation of a relation for debugging.
 *
 * @param array $relationArrEntry An associative array representing a single relation.
 * @return void
 */
function printRelation($relationArrEntry)
{
	 
	echoN("---SUBJ:<b>".$relationArrEntry['SUBJECT']."</b> VERB:".$relationArrEntry['VERB']." OBJ:<b>".$relationArrEntry['OBJECT']."</b>");
}


/**
 * Adds a new relation to the relations array if it's unique, otherwise increments its frequency.
 *
 * @param array  &$relationArr        The array of relations, passed by reference.
 * @param string $type                The type of the relation (e.g., "TAXONOMIC", "NON-TAXONOMIC").
 * @param string $subject             The subject of the relation.
 * @param string $verbSimple          The simple form of the verb.
 * @param string $object              The object of the relation.
 * @param string $posPattern          The Part-of-Speech pattern associated with the relation.
 * @param string $verbEngTranslation  The English translation of the verb.
 * @param string $verbUthmani         The Uthmani script form of the verb.
 * @return bool True if a new relation was added, false if an existing one was updated.
 */
function addNewRelation(&$relationArr,$type,$subject,$verbSimple,$object,$posPattern,$verbEngTranslation,$verbUthmani)
{
	$newRelation= array("TYPE"=>$type,"SUBJECT"=>trim($subject),
			"VERB"=>trim($verbSimple),
			"OBJECT"=>trim($object),
			"POS_PATTERN"=>$posPattern,
			"FREQ"=>1,
			"VERB_ENG_TRANSLATION"=>trim($verbEngTranslation),
			"VERB_UTHMANI"=>trim($verbUthmani));
	
	printRelation($newRelation);
	
		
	$relationHash = md5($newRelation['SUBJECT'].$newRelation['VERB'].$newRelation['OBJECT']);
		
	if ( !isset($relationArr[$relationHash]))
	{
	
		$relationArr[$relationHash]=$newRelation;
		return true;
	}
	else
	{
		$relationArr[$relationHash]['FREQ']++;
		return false;
	}
}

/**
 * Processes and adds a new relation to the relations array, handling various normalizations and translations.
 *
 * @param array  &$relationsArr      The array of relations, passed by reference.
 * @param string $type               The type of the relation.
 * @param string $subject            The subject of the relation (can be Uthmani or simple).
 * @param string $verb               The verb of the relation (can be Uthmani or simple).
 * @param string $object             The object of the relation (can be Uthmani or simple).
 * @param string $joinedPattern      The joined POS pattern for the relation.
 * @param string $verbEngTranslation Optional English translation of the verb. If empty, it will be generated.
 * @param string $fullVerbQuranWord  Optional full Quranic word for the verb, used for translation.
 * @return bool|null The result from addNewRelation, or false if subject or object is empty.
 */
function addRelation(&$relationsArr,$type, $subject,$verb,$object,$joinedPattern,$verbEngTranslation="",$fullVerbQuranWord="")
{
	global $WORDS_TRANSLATIONS_AR_EN;
	global $is_a_relation_name_en;
	
		
	
	if ( empty($subject) || empty($object) )
	{
		return false;
	}
	
	
	// make shallow last resort, since it spoils words and lead to duplicate oncepts
	if ( !isSimpleQuranWord($subject) )
	{
		//CONVERT UTHMANI TO SIMPLE
		$subjectSimple = getItemFromUthmaniToSimpleMappingTable($subject);
			
		// IF NOT CORRESPONDING SIMPLE WORD, CONVERT USING SHALLOW CONVERSION ALGORITHM
		if ( empty($subjectSimple))
		{
			$subjectSimple = shallowUthmaniToSimpleConversion($subject);
		}
	}
	else 
	{
		$subjectSimple = $subject;
	}

	// SAME AS ABOVE BUT FOR OBJECT
	if ( !isSimpleQuranWord($object) )
	{
		$objectSimple = getItemFromUthmaniToSimpleMappingTable($object);

		//object simple to avoid null in case when not in the mapping table
		if ( empty($objectSimple))
		{
			$objectSimple = shallowUthmaniToSimpleConversion($object);
		}
	}
	else
	{
		$objectSimple = $object;
	}
		
	
	$verbUthmani = $verb;
	$verbSimple = "";
	
	///////// VERB TRANSLATION
	if ( empty($verbEngTranslation))
	{
		$verbEngTranslation ="";
	
		// SINGLE WORD VERB
		if ( !isMultiWordStr($verb))
		{
			$verb = trim($verb);
			
			$translatableVerb = $fullVerbQuranWord;
			
			// VERB IS SIMPLE
			if ( isSimpleQuranWord($verb) )
			{
				$translatableVerb = getItemFromUthmaniToSimpleMappingTable($fullVerbQuranWord);

			}
			else
			{

				$verbSimple = getItemFromUthmaniToSimpleMappingTable($verb);;
			}
			
			$verbEngTranslation = cleanEnglishTranslation($WORDS_TRANSLATIONS_AR_EN[$translatableVerb]);
			
			//IF NOT IN TRANSLATION TABLE - EX: ONE OF THE SEGMENTS TRIMMED
			if ( empty($verbEngTranslation))
			{
				// CHECK IF IS ALSO NOTO IN TRANSLATION ENTRY
				if (!isFoundInTranslationTable($translatableVerb,"VERB"))
				{
					

					// TRANSLATE USING MICROSOFT API
					$verbEngTranslation = translateText($translatableVerb,"ar","en");
					
					// ADD TO QA CUSTOM TRANSLATION TABLE
					addTranslationEntry($verbEngTranslation, "VERB", $translatableVerb,"AR");
					
					//no need
					//persistTranslationTable();
				}
				else
				{
					$customTranslationEntryArr =getTranlationEntryByEntryKeyword($translatableVerb);
					
					$verbEngTranslation = $customTranslationEntryArr['EN_TEXT'];
				}
			}
		}
		// MUTIWORD VERB (PHRASE) such as negated verbs
		else
		{
			
			//SPLIT PHRASE
			$verbPhraseArr = preg_split("/ /", $verb);
				
			foreach($verbPhraseArr as $verbPart)
			{
				
				$translatableVerb = $verbPart;
				
				// IF SIMPLE
				if ( isSimpleQuranWord($verbPart) )
				{
					//GET UTHMANI WORD TO BE ABEL TO TRANSLATE
					$translatableVerb = getItemFromUthmaniToSimpleMappingTable($verbPart);
				}
				else
				{
					// GET SIMPLE WORD TO BE ADDED IN RELATION META
					$simplePart = getItemFromUthmaniToSimpleMappingTable($verbPart);
					
					//if not in translation table, use shalow conversion
					if ( empty($simplePart))
					{
						$simplePart = shallowUthmaniToSimpleConversion($verbPart);
					}
					
					$verbSimple = $verbSimple." ".$simplePart;
					
					// THIS VARIABLE NEEDED FOR TRANSLATION
					$translatableVerb = $simplePart;
				}
				
				// TRANSLATE
				$verbPartTranslated = cleanEnglishTranslation($WORDS_TRANSLATIONS_AR_EN[$translatableVerb]);
				
				//IF NOT IN TRANSLATION TABLE - EX: ONE OF THE SEGMENTS TRIMMED
				if ( empty($verbPartTranslated))
				{
					// CHECK IF IS ALSO NOTO IN TRANSLATION ENTRY
					if (!isFoundInTranslationTable($verbPart,"VERB"))
					{
							

						
						// TRANSLATE USING MICROSOFT API
						$verbPartTranslated = translateText($verbPart,"ar","en");
							
						// ADD TO QA CUSTOM TRANSLATION TABLE
						addTranslationEntry($verbPartTranslated, "VERB", $verbPart,"AR");
							
						
						//persistTranslationTable();
					}
					else
					{
						$customTranslationEntryArr =getTranlationEntryByEntryKeyword($verbPart);
							
						$verbPartTranslated = $customTranslationEntryArr['EN_TEXT'];
					}
				}
				
				// TRANSLATION ACCUMILATION
				$verbEngTranslation = $verbEngTranslation . " " .$verbPartTranslated;
			}
		}
	}
	
	if ( $verbEngTranslation!="is kind of" && $verbEngTranslation!="part of" && $verbEngTranslation!=$is_a_relation_name_en)
	{
		//$verbEngTranslation = removeBasicEnglishStopwordsNoNegation($verbEngTranslation);
	}
		
	$verbSimple = trim($verbSimple);
	
	if ( empty($verbSimple))
	{
		$verbSimple = removeTashkeel(shallowUthmaniToSimpleConversion($verbUthmani));
	}

		
	return addNewRelation($relationsArr,$type,$subjectSimple,$verbSimple,$objectSimple,$joinedPattern,$verbEngTranslation,$verbUthmani);
}

/**
 * Resolves pronouns for a given QAC location by looking up their antecedents in the Qurana model.
 *
 * @param string $qacLocation The QAC location string (e.g., "3:146:11").
 * @return array An array of resolved concept names for the pronoun.
 */
function resolvePronouns($qacLocation)
{
	global $MODEL_QURANA;
	$pronArr = array();
	$index=0;
	//echoN($qacLocation);
	//if ( $qacLocation=="3:146:11")
	//preprint_r($MODEL_QURANA['QURANA_PRONOUNS']);
	foreach($MODEL_QURANA['QURANA_PRONOUNS'][$qacLocation] as $coneptArr)
	{

		$coneptId = $coneptArr['CONCEPT_ID'];
		$conceptName = $MODEL_QURANA['QURANA_CONCEPTS'][$coneptId]['AR'];

		echoN($conceptName);

		// qurana null concept
		//if ( $conceptName=="null") continue;

		$pronArr[$index++]=$conceptName;
	}

	return $pronArr;
}

/**
 * Flushes or processes a set of collected concepts to form relations.
 * This function is used within a loop to create relations from concepts that have been identified in sequence.
 * It handles cases with 2 or 3 concepts and resets the state for the next iteration.
 *
 * @param array  &$relationsArr   The main array of relations, passed by reference.
 * @param array  &$conceptsArr    The array of concepts collected for the current relation, passed by reference.
 * @param string &$verb           The verb connecting the concepts, passed by reference.
 * @param string &$lastSubject    The last subject identified, used for context, passed by reference.
 * @param string $ssPoSPattern    The Part-of-Speech pattern for the sub-sentence.
 * @param int    &$filledConcepts A counter for the number of filled concepts, passed by reference.
 * @return void
 */
function flushProperRelations(&$relationsArr,&$conceptsArr,&$verb,&$lastSubject,$ssPoSPattern,&$filledConcepts)
{


	if ( count($conceptsArr)>=2   )
	{

		if (empty($verb))
		{
			$verb = "n/a";
		}
			
			

		if ( $conceptsArr[0]!=$conceptsArr[1])
		{
			$type = "NON-TAXONOMIC";
			addRelation($relationsArr,$type, $conceptsArr[0],$verb,$conceptsArr[1],$ssPoSPattern);

			if ( count($conceptsArr)>2 )
			{
				addRelation($relationsArr,$type, $conceptsArr[1],"n/a",$conceptsArr[2],$ssPoSPattern);
				addRelation($relationsArr,$type, $conceptsArr[0],"n/a",$conceptsArr[2],$ssPoSPattern);
			}
		}
			
		$conceptsArr=array();
		$verb = null;
		$filledConcepts=0;
	}
		
		
	if ( count($conceptsArr)==1 && !empty($verb) && !empty($lastSubject) && $conceptsArr[0]!=$lastSubject)
	{

		//echoN("||||".$conceptsArr[0]."|".$lastSubject);




		$temp = $conceptsArr[0];
		$conceptsArr[0] = $lastSubject;
		$conceptsArr[1] = $temp;


		// many problems
		if ( $conceptsArr[0]!=$conceptsArr[1])
		{
			$type = "NON-TAXONOMIC";
			addRelation($relationsArr,$type, $conceptsArr[0],$verb,$conceptsArr[1],$ssPoSPattern);
		}
			
			

			
		$conceptsArr=array();
		$verb = null;

		$filledConcepts=0;
	}
}
	
	

/**
 * Finds a concept's simple word name by one of its Uthmani segments.
 *
 * @param array  $conceptsArr The array of concepts to search through.
 * @param string $segment     The Uthmani segment to search for.
 * @return string|false The simple word of the concept if found, false otherwise.
 */
function getConceptBySegment($conceptsArr, $segment)
{
	foreach ($conceptsArr as $conceptName=>$conceptArr)
	{
		$extraArr = $conceptArr['EXTRA'];
		$simpleWord = $extraArr['SIMPLE_WORD'];
			
		foreach ($extraArr['SEG'] as $uthmaniSegment=>$simpleName)
		{
			//echoN("$uthmaniSegment==$segment");

			if ( $uthmaniSegment==$segment)
			{
					
				return $simpleWord;
			}
		}

			
	}

	return false;
}

/**
 * Finds a concept's simple word name by its lemma.
 *
 * @param array  $conceptsArr The array of concepts to search through.
 * @param string $lemma       The lemma to search for.
 * @return string|false The simple word of the concept if found, false otherwise.
 */
function getConceptByLemma($conceptsArr, $lemma)
{
	foreach ($conceptsArr as $conceptName=>$conceptArr)
	{
		$extraArr = $conceptArr['EXTRA'];
		$simpleWord = $extraArr['SIMPLE_WORD'];


		//echoN("$uthmaniSegment==$segment");

		if ( $extraArr['LEM']==$lemma)
		{

			return $simpleWord;
		}
			


	}

	return false;
}

/**
 * Attempts to determine a concept's type (e.g., its parent class) from an English description text.
 * It uses Part-of-Speech tagging to find patterns like "is a [type]".
 *
 * @param string $abstract The English description text.
 * @return string|false The determined concept type if found, false otherwise.
 */
function getConceptTypeFromDescriptionText($abstract)
{
	$matches = array();
		

	$taggesSentenceArr = posTagText($abstract);

	//printTag($taggesSentenceArr);

	$counter =0;
	reset($taggesSentenceArr);
	while(current($taggesSentenceArr))
	{
		$currentTagArr = current($taggesSentenceArr);
		$nextTagArr = next($taggesSentenceArr);
			
		if ( ($currentTagArr['tag']=="VBZ" || $currentTagArr['tag']=="VBD" )
		&& $nextTagArr['tag']=="DT")
		{
			$thirdTagArr = next($taggesSentenceArr);


			if ( ($thirdTagArr['tag']=="NN" || $thirdTagArr['tag']=="VBG" )&& strtolower($thirdTagArr['token'])!="name")
			{
				$forthTagArr = next($taggesSentenceArr);
				if ( !empty($forthTagArr) && $forthTagArr['tag']=="IN"  )
				{
					//echoN("########".$nextTagArr['token']);
					return $thirdTagArr['token'];
				}
			}

		}
			
		if ( $counter++ > 20 ) return false;
			
			
			
	}


	return false;
		
}


/**
 * Retrieves and processes words from the Quranic Arabic Corpus (QAC) based on a specific Part-of-Speech (POS) tag.
 * The results are grouped by lemma and populated into the final terms array.
 *
 * @param array  &$finalTerms The array of final terms to populate, passed by reference.
 * @param string $POS         The POS tag to filter by (e.g., "N", "V").
 * @return array The populated final terms array.
 */
function getWordsByPos(&$finalTerms,$POS)
{

	global $LEMMA_TO_SIMPLE_WORD_MAP;
	 
	 
	$qacPosEntryArr = getModelEntryFromMemory("AR","MODEL_QAC","QAC_POS",$POS);
	
	$QURAN_TEXT = getModelEntryFromMemory("AR", "MODEL_CORE", "QURAN_TEXT", "");
	
	$TOTALS = getModelEntryFromMemory("AR", "MODEL_CORE", "TOTALS", "");
	
	$PAUSEMARKS = $TOTALS['PAUSEMARKS'];
	
	$WORDS_FREQUENCY = getModelEntryFromMemory("AR", "MODEL_CORE", "WORDS_FREQUENCY", "");
	
	// Get all segment in QAC for that PoS
	foreach($qacPosEntryArr as $location => $segmentId)
	{

		$qacMasterTableEntry = getModelEntryFromMemory("AR","MODEL_QAC","QAC_MASTERTABLE",$location);
		
		// get Word, Lema and root
		$segmentWord = $qacMasterTableEntry[$segmentId-1]['FORM_AR'];
		$segmentWordLema = $qacMasterTableEntry[$segmentId-1]['FEATURES']['LEM'];
		$segmentWordRoot = $qacMasterTableEntry[$segmentId-1]['FEATURES']['ROOT'];
		$verseLocation = substr($location,0,strlen($location)-2);
		//$segmentWord = removeTashkeel($segmentWord);


		// get word index in verse
		$wordIndex = (getWordIndexFromQACLocation($location));


		//$segmentFormARimla2y = $UTHMANI_TO_SIMPLE_WORD_MAP_AND_VS[$segmentWord];

		// get simple version of the word index
		$imla2yWordIndex = getImla2yWordIndexByUthmaniLocation($location);


		// get verse text
		$verseText = getVerseByQACLocation($QURAN_TEXT,$location);
		 

		 
		//echoN("|$segmentWord|$imla2yWord");
		$segmentWordNoTashkeel = removeTashkeel($segmentWordLema);
		 
		$superscriptAlef = json_decode('"\u0670"');
		$alefWasla = "ٱ"; //U+0671
		 
		//$imla2yWord = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];
		 
		 
		// this block is important since $LEMMA_TO_SIMPLE_WORD_MAP is not good for  non $superscriptAlef words
		// ex زيت lemma is converted to زيتها which spoiled the ontology concept list results
		if(mb_strpos($segmentWordLema, $superscriptAlef) !==false
		|| mb_strpos($segmentWordLema, $alefWasla) !==false )
		{

			$imla2yWord = getItemFromUthmaniToSimpleMappingTable($segmentWordLema);

			if (empty($imla2yWord))
			{
				$imla2yWord = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];
			}



		}
		else
		{
			$imla2yWord = getItemFromUthmaniToSimpleMappingTable($segmentWordLema);

			if ( empty($imla2yWord))
			{
				$imla2yWord = shallowUthmaniToSimpleConversion($segmentWordLema);//$segmentWordNoTashkeel;
					
			}
		}
		 
		 
		 
		/// in case the word was not found after removing tashkeel, try the lema mappign table
		$termWeightArr = $MODEL_CORE['WORDS_FREQUENCY']['WORDS_TFIDF'][$imla2yWord];


		 
		// NOT WORKING BECAUSE LEMMAS WILL NOT BE IN SIMPLE WORDS LIST و الصابيئن =>صَّٰبِـِٔين
		// if the word after removing tashkeel is not found in quran simple words list, then try lemma table
		/*if (!isset($MODEL_CORE['WORDS_FREQUENCY']['WORDS'][$imla2yWord]) )
		 {
		 $imla2yWord = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];

		 if ( empty($imla2yWord) )
		 {
		 echoN($segmentWordLema);
		 echoN($imla2yWord);
		 preprint_r($LEMMA_TO_SIMPLE_WORD_MAP);
		 preprint_r($MODEL_CORE['WORDS_FREQUENCY']['WORDS']);
		 exit;
		 }
		 }*/

		 
		if ( empty($termWeightArr))
		{
			//only for weight since the lema table decrease qurana matching
			$imla2yWordForWeight = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];
			$termWeightArr = $WORDS_FREQUENCY['WORDS_TFIDF'][$imla2yWordForWeight];


		}
		 
		$termWeight = $termWeightArr['TFIDF'];
		////////////////////////////////////////////

		$termWord = $segmentWordLema;//$imla2yWord;//"|$segmentWord| ".$imla2yWord ." - $location:$segmentId - $wordIndex=$imla2yWordIndex";
		 
		if ( !isset($finalTerms[$termWord]))
		{
			$finalTerms[$termWord] = generateEmptyConceptMetadata();

			$finalTerms[$termWord]['LEM'] = $segmentWordLema;
			$finalTerms[$termWord]['POS'] = $POS;
			$finalTerms[$termWord]['SIMPLE_WORD'] = $imla2yWord;
			$finalTerms[$termWord]['ROOT'] = $segmentWordRoot;
			$finalTerms[$termWord]['WEIGHT'] = $termWeight;


		}
		 
		$finalTerms[$termWord]["FREQ"]=$finalTerms[$termWord]["FREQ"]+1;
			
		if ( !isset($finalTerms[$termWord]["SEG"][$segmentWord]) )
		{
			$finalTerms[$termWord]["SEG"][$segmentWord]=$imla2yWord;
				
		}
			
		if ( !isset($finalTerms[$termWord]["POSES"][$POS]))
		{
			$finalTerms[$termWord]["POSES"][$POS]=1;
		}
			
			
		 
		 





	}
	 
	return $finalTerms;
}

/**
 * Loads a list of excluded items of a specific type from a file.
 *
 * @param string $type The type of exclusion list to load (e.g., "concepts", "verbs").
 * @return array An associative array of the excluded items.
 */
function loadExcludesByType($type)
{
	$fileArr = file("../data/ontology/extraction/cleaner/excluded.$type",FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
	
	$excludedItemsArr = array();
	
	foreach($fileArr as  $itemName)
	{

		$itemName = trim($itemName);
		$excludedItemsArr[$itemName]=1;
		
	}
	
	return $excludedItemsArr;
	
}

/**
 * Loads the list of excluded synonyms from a file.
 *
 * @return array An associative array of the excluded synonyms.
 */
function loadExcludedSynonymssArr()
{
	$fileArr = file("../data/ontology/extraction/excluded.synonyms",FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

	$EXCLUDED_SYN = array();

	foreach($fileArr as  $synonym)
	{
		$synonym = trim($synonym);
		$EXCLUDED_SYN[$synonym]=1;

	}

	return $EXCLUDED_SYN;

}

/**
 * Converts a class name into an XML-friendly string by replacing spaces with underscores.
 *
 * @param string $className The input class name.
 * @return string The XML-friendly class name.
 */
function getXMLFriendlyString($className)
{
	return strtr($className, " ", "_");
}

/**
 * Strips the ontology namespace from a class name.
 *
 * @param string $className The class name, potentially with a namespace prefix.
 * @return string The class name without the namespace.
 */
function stripOntologyNamespace($className)
{
	global $qaOntologyNamespace;
	
	$hashLocation = strpos($className,"#");
	if ($hashLocation!==false)
	{
		$className = substr($className,$hashLocation+1);
	}
	else
	{
		$className = str_replace(substr($qaOntologyNamespace,0,-1), "", $className);
	}
	
	return $className;
}

/**
 * Checks if a concept has any subclasses within the given set of relations.
 *
 * @param array  $relationsArr The array of all relations.
 * @param string $concept      The concept to check.
 * @return bool True if the concept has subclasses, false otherwise.
 */
function conceptHasSubclasses($relationsArr,$concept)
{
	global $is_a_relation_name_ar;
	
	foreach($relationsArr as $hash => $relationArr)
	{
		$relationsType = $relationArr['TYPE'];
	
		$subject = 	$relationArr['SUBJECT'];
		$object = $relationArr['OBJECT'];
		$verbAR = $relationArr['VERB'];
		
		
	
			
		// IF IT IS AN IS-A RELATION
		if ( $verbAR==$is_a_relation_name_ar && $concept==$object)
		{
			return true;
		}
	}
	
	return false;
}
/**
 * Checks if a concept has any parent classes within the given set of relations.
 *
 * @param array  $relationsArr The array of all relations.
 * @param string $concept      The concept to check.
 * @return bool True if the concept has parent classes, false otherwise.
 */
function conceptHasParentClasses($relationsArr,$concept)
{
	global $is_a_relation_name_ar;

	foreach($relationsArr as $hash => $relationArr)
	{
		$relationsType = $relationArr['TYPE'];

		$subject = 	$relationArr['SUBJECT'];
		$object = $relationArr['OBJECT'];
		$verbAR = $relationArr['VERB'];
			
		// IF IT IS AN IS-A RELATION
		if ( $verbAR==$is_a_relation_name_ar && $concept==$subject)
		{
			return true;
		}
	}

	return false;
}


/**
 * Builds a unique hash ID for a relation based on its subject, verb, and object.
 *
 * @param string $subject The subject of the relation.
 * @param string $verb    The verb of the relation.
 * @param string $object  The object of the relation.
 * @return string The MD5 hash of the concatenated subject, verb, and object.
 */
function buildRelationHashID($subject,$verb,$object)
{
	return md5("$subject,$verb,$object");
}

/**
 * Checks if a given word is part of a verb in the ontology's verb index.
 *
 * @param string $word The word to check.
 * @param string $lang The language of the word ('EN' or 'AR').
 * @return array|false The verb array from the index if a match is found, false otherwise.
 */
function isWordPartOfAVerbInVerbIndex($word,$lang)
{

	
	$verbIndexIterator = getAPCIterator("ALL\/MODEL_QA_ONTOLOGY\/VERB_INDEX\/.*");
	
	foreach($verbIndexIterator as $verbIndexCursor )
	{
		$verbWord = getEntryKeyFromAPCKey($verbIndexCursor['key']);
	
		$verbArr = $verbIndexCursor['value'];
	

		if ( $lang=="EN")
		{
			$verbWord = strtolower($verbWord);
			
		}
		
		if ( mb_strpos($verbWord, $word)!==false) 
		{
			//echoN("|$verbWord| |$word|".( mb_strpos($verbWord, $word)!==false));
			return $verbArr;
		}
	}
	
	return false;
}

/**
 * Handles the creation of a new concept that was identified from a relation's subject or object.
 * This function is typically called when a subject or object in a relation does not already exist in the main concepts list.
 *
 * @param array   &$finalConcepts             The main array of final concepts, passed by reference.
 * @param string  $subjectOrObject            The subject or object string to be handled.
 * @param string  $conceptLocationInRelation  Indicates if the concept is a "SUBJECT" or "OBJECT".
 * @param int     &$notInCounceptsCounter     A counter for concepts not found, passed by reference.
 * @param array   &$statsUniqueSubjects       An array to track unique subjects, passed by reference.
 * @return void
 */
function handleNewConceptFromRelation(&$finalConcepts,$subjectOrObject,$conceptLocationInRelation,&$notInCounceptsCounter,&$statsUniqueSubjects)
{
	global  $WORDS_TRANSLATIONS_AR_EN;
	
	$subjectOrObjectFlag =  null;
		
	// SUBJECT NOT IN MASTER CONCEPTS LIST
	if ( !isset($finalConcepts[$subject]) )
	{
		
		if ( $conceptLocationInRelation=="SUBJECT")
		{
			echoN("NOT IN CONCEPTS:S:$subjectOrObject");
		}
		else
		{
			echoN("NOT IN CONCEPTS:O:$subjectOrObject");
		}
		$notInCounceptsCounter++;
			
		$statsUniqueSubjects[$subjectOrObject]=1;

	
	}
	

	
	$termsArr = getTermArrBySimpleWord($finalTerms,$subjectOrObject);
		
	$freq = $termsArr['FREQ'];
		
	
		
	$isQuranaPhraseConcept = false;
	
	//echoN("^&&*:".(strpos($subjectOrObject," ")!==false));
	
	if( isMultiWordStr($subjectOrObject))
	{
		$quranaConceptArr = getQuranaConceptEntryByARWord($subjectOrObject);
	
	
		$engTranslation = ucfirst($quranaConceptArr['EN']);
			
		echoN("^^$subjectOrObject");
		$isQuranaPhraseConcept = true;
	}
	else
	{
		$uthmaniWord = getItemFromUthmaniToSimpleMappingTable($subjectOrObject);
		$engTranslation = ucfirst(cleanEnglishTranslation($WORDS_TRANSLATIONS_AR_EN[$uthmaniWord]));
	}
		
		
		
	addNewConcept($finalConcepts, $subjectOrObject, "A-BOX", "POPULATION_FROM_RELATIONS", $freq, $engTranslation);
	
	$finalConcepts[$subjectOrObject]['EXTRA']['POS']=$subjectOrObjectFlag;
	$finalConcepts[$subjectOrObject]['EXTRA']['WEIGHT']=$termsArr['WEIGHT'];
	
	if ( $isQuranaPhraseConcept)
	{
		echoN($isQuranaPhraseConcept."||||$subjectOrObject");
		$finalConcepts[$subjectOrObject]['EXTRA']['IS_QURANA_NGRAM_CONCEPT']=true;
	}
}


/**
 * Checks if an extended query array (word => POS tag) contains a significant verb.
 *
 * @param array $extendedQueryArr The extended query array.
 * @return bool True if a verb other than "is" or "are" is found, false otherwise.
 */
function doesQuestionIncludesVerb($extendedQueryArr)
{
	foreach($extendedQueryArr as $word => $pos)
	{
		if ( posIsVerb($pos))
		{
			if ( $word!="is" && $word!="are")
			{
				return true;
			}
		}
	}
	return false;
}

/**
 * Calculates a "richness score" for a concept based on the length of its printed representation.
 * This is a proxy for the amount of information available for the concept.
 *
 * @param array $coneptArr The concept array.
 * @return int The length of the string representation of the concept array.
 */
function getConceptRichnessScore($coneptArr)
{
	return strlen(print_r($coneptArr,true));
}

/**
 * Updates all occurrences of a concept's name within the relations array.
 * This is used when merging concepts, to ensure all relations point to the new concept name.
 *
 * @param array  &$relationsArr The array of relations, passed by reference.
 * @param string $nameFrom      The old concept name.
 * @param string $nameTo        The new concept name.
 * @return void
 */
function updateNameInAllRelations(&$relationsArr, $nameFrom, $nameTo)
{
	$relationsArrComp = $relationsArr;
	
	foreach($relationsArr as $hash => $relationArr)
	{
		$relationsType = $relationArr['TYPE'];
	
		$subject = 	$relationArr['SUBJECT'];
		$object = $relationArr['OBJECT'];
		$verbAR = $relationArr['VERB'];
		
			
		if ( $subject=="$nameFrom")
		{
			$relationsArr[$hash]['SUBJECT']=$nameTo;
		}
		if ( $object=="$nameFrom")
		{
			$relationsArr[$hash]['OBJECT']=$nameTo;
		}
			
			
		$newHash = md5($relationsArr[$hash]['SUBJECT'].$relationsArr[$hash]['VERB'].$relationsArr[$hash]['OBJECT']);
			
		//echoN("###  $newHash $hash $subject $verbAR $object");
		
		if ( $newHash!=$hash)
		{
			$relationsArrComp[$newHash] = $relationsArr[$hash];
			unset($relationsArrComp[$hash]);
		}
	}
	
	 $relationsArr = $relationsArrComp;
}

/**
 * Finds all concepts from the ontology that are present in a given text.
 *
 * @param string $text The text to search for concepts in.
 * @param string $lang The language of the text ('EN' or 'AR').
 * @return array An array of concept objects found in the text.
 */
function getConceptsFoundInText($text,$lang)
{
	

	global $thing_class_name_ar, $is_a_relation_name_ar;


	
	$conceptsInTextArr = array();



		
		$textWordsArr = preg_split("/ /",$text);
	
		foreach($textWordsArr as $index=>$word)
		{
				
				
			if ( $lang == "EN")
			{
				$word = cleanAndTrim($word);
				$word = strtolower($word);
				
				
				
				// translate English name to arabic concept name/id
				//$wordConveretedToConceptID = $MODEL_QA_ONTOLOGY['CONCEPTS_EN_AR_NAME_MAP'][$word];
				
				$wordConveretedToConceptID  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS_EN_AR_NAME_MAP", $word);
			}
			else
			{
					
				$wordConveretedToConceptID = convertWordToConceptID($word);
			}
				
			//echoN($wordConveretedToConceptID);
			
			if ( modelEntryExistsInMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $wordConveretedToConceptID) )
			{
				//preprint_r($MODEL_QA_ONTOLOGY['CONCEPTS'][$wordConveretedToConceptID]);exit;
				//echoN($wordConveretedToConceptID);

				//$mainConceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$wordConveretedToConceptID];
				
				$mainConceptArr = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $wordConveretedToConceptID);

				$conceptLabelAR = $mainConceptArr['label_ar'];
				$conceptLabelEN = $mainConceptArr['label_en'];
				$conceptFrequency = $mainConceptArr['frequency'];
				$conceptWeight = $mainConceptArr['weight'];

				$finalNodeLabel = $conceptLabelAR;

				if ( $lang == "EN")
				{
					$finalNodeLabel = $conceptLabelEN;
				}


				if (  $wordConveretedToConceptID==$thing_class_name_ar) continue;

					
	

				$conceptsInTextArr[$wordConveretedToConceptID]= createNewConceptObj($nodeSerialNumber,$lang, $finalNodeLabel, $mainConceptArr,$randomXLocation,$randomYLocation,1);
	
					

			}
				
		}
	

	return $conceptsInTextArr;

}

?>
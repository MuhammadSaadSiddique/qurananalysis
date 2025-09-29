<?php
/**
 * Lightweight Search Results AJAX Service
 *
 * This script serves as a lightweight AJAX endpoint for processing search queries.
 * It is similar to `search/index.php` but returns a more focused set of results,
 * making it suitable for contexts where only the verse list is required without the
 * accompanying visualizations.
 *
 * The script performs the following actions:
 * 1.  It includes `query.handling.common.php` to parse the query, perform
 *     query expansion, and retrieve a scored list of relevant documents.
 * 2.  It includes `search.result.statement.inc.php` to show a summary of the results.
 * 3.  It calls `printResultVerses()` to render the list of matching verses.
 *
 * Unlike the main search service, this script does not generate the ontology graph,
 * word cloud, or statistics charts.
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

require_once("../libs/search.lib.php");
require_once("../libs/graph.lib.php");


require_once("query.handling.common.php");






?>

<?php require_once('search.result.statement.inc.php')?>

<?php 
//// PRINT RESULT VERSES
printResultVerses($scoringTable,$lang,$direction,$query,$isPhraseSearch,$isQuestion,$script);
?>





<?php
/**
 * Data Model Cache Reloader
 *
 * This administrative script is a developer utility used to force a complete
 * refresh of the application's data models stored in the APC cache.
 *
 * It performs the following actions:
 * 1. Clears the entire APC user cache.
 * 2. Triggers the `loadModels()` function to parse all raw data files and
 *    rebuild the data models from scratch.
 * 3. Stores the newly built models back into the APC cache.
 *
 * This is essential during development when underlying data sources or the
 * model generation logic in `model.loader.php` have been modified. It outputs
 * cache memory information before and after the process for debugging purposes.
 *
 * @package QuranAnalysis
 * @author Karim Ouda
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
require("../global.settings.php");
require_once(dirname(__FILE__)."/../libs/core.lib.php");

if ( isDevEnviroment())
{
	printHTMLPageHeader();
}
$cacheInfo = apc_cache_info('user');

echoN("CACHE MEM BEFROE:".$cacheInfo['mem_size']);
apc_clear_cache();

$cacheInfo = apc_cache_info('user');

echoN("CACHE MEM AFTER CLEAR:".$cacheInfo['mem_size']);

loadModels("core,search,qac,qurana,wordnet","EN");

$cacheInfo = apc_cache_info('user');
echoN("CACHE MEM AFTER RELOAD:".$cacheInfo['mem_size']);

preprint_r($cacheInfo);


echoN("DONE");

?>





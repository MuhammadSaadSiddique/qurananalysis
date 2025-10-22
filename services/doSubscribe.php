<?php
/**
 * Mailing List Subscription Service
 *
 * This script handles new user subscriptions to the mailing list. It is designed
 * to be called via an AJAX POST request from the subscription form.
 *
 * It expects the following POST parameters:
 * - `name`: The subscriber's name.
 * - `email`: The subscriber's email address.
 * - `title`: The subscriber's title/occupation.
 * - `entity`: The subscriber's organization or entity.
 *
 * The script inserts the new subscriber's information into the `EmailList` table
 * in the SQLite database and then includes `sendEmail.inc.php` to send an email
 * notification about the new subscription. It returns "DONE" on success or an
 * error message on failure.
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
error_reporting ( E_ALL );

include (dirname ( __FILE__ ) . "/../libs/core.lib.php");
include (dirname ( __FILE__ ) . "/../dal/SQLite3DataLayer.class.php");

$email = $_POST ['email'];
$name = $_POST ['name'];
$title = $_POST ['title'];
$entityVal = $_POST ['entity'];


if (empty ( $email )) 
{
	echo "ERROR";
	exit ();
}

$dbPath = dirname ( __FILE__ ) . "/../data/databases/main.sqlite";

$sqliteDBObj = new SQLite3DataLayer ();

$sqliteDBObj->openDB ( $dbPath, "rw" );

$sqliteDBObj->execOnewayQuery ( MAILING_LIST_TABLE );

$sqliteDBObj->execOnewayQuery ( "INSERT INTO EmailList " . "(name, title, entity, email) " . "VALUES " . "('$name', '$title','$entityVal','$email')" );

$error = handleDBError ( $sqliteDBObj );

if (empty ( $error )) 
{
	$body = "New subscription<br>Name:$name<br>Title:$title<br>Entity:$entityVal<br>Email:$email<br>";
	include ("sendEmail.inc.php");
	
	echo "DONE";
} 
else 
{
	echo "Error occured! please report it using the contact page".$error;
}

?>

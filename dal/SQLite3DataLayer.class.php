<?php
/**
 * SQLite3 Data Access Layer
 *
 * This file provides a simple data access layer (DAL) for interacting with an
 * SQLite3 database. It encapsulates database connection, querying, and error
 * handling logic into a reusable class.
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

define ( "DATABASE_LOCK_ERROR", "Database is being updated ! try again later" );
define ( "DATABASE_CONN_ERROR", "Could not connect to DB !" );
define ( 'SQLITE3_OPEN_SHAREDCACHE', 0x00020000 );

define("MAILING_LIST_TABLE",
"CREATE TABLE IF NOT EXISTS EmailList " .
"(subscriberId INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT,  title TEXT, entity TEXT, email TEXT, UNIQUE(email)  )");

define("FEEDBACK_TABLE",
"CREATE TABLE IF NOT EXISTS Feedback " .
"(feedbackId INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT,email TEXT, type TEXT, feedback_text TEXT, UNIQUE(email,feedback_text)  )");


/**
 * A class to handle interactions with an SQLite3 database.
 */
class SQLite3DataLayer
{
	/**
	 * @var SQLite3 The database connection object.
	 */
	public $databaseConn = null;
	
	/**
	 * Checks if the database connection is active.
	 *
	 * @return bool True if connected, false otherwise.
	 */
	public function isConnected()
	{
		return ($this->databaseConn != null);
	}
	
	/**
	 * Opens a connection to the SQLite database.
	 *
	 * @param string $dbPath The path to the SQLite database file.
	 * @param string $mode   The mode to open the database in ('ro' for read-only, 'rw' for read-write).
	 * @return SQLite3|null The database connection object on success, or null on failure.
	 */
	public function openDB($dbPath, $mode = "ro")
	{
		if ($this->databaseConn == null) {
			
	
			
			if ($mode == "rw") 
			{
				$mode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
			}
			else if ($mode == "ro") 
			{
				$mode = SQLITE3_OPEN_READONLY | SQLITE3_OPEN_SHAREDCACHE;
			}
			
			try 
			{
				
				$this->databaseConn = new SQLite3 ($dbPath, $mode );
				
				$this->databaseConn->busyTimeout ( 3000 );
				
				$res = $this->execOnewayQuery ( 'PRAGMA temp_store=MEMORY;' );
				$this->execOnewayQuery ( 'PRAGMA journal_mode=MEMORY;' );
				$this->execOnewayQuery ( 'PRAGMA cache_size=10000;' );
				$this->execOnewayQuery ( 'PRAGMA read_uncommitted=1' );
			} 
			catch ( Exception $e )
			{
				
				$this->databaseConn = null;
				return null;
			}
		}
		
		
		return $this->databaseConn;
	}
	
	/**
	 * Executes a query that returns multiple rows.
	 *
	 * @param string $sql    The SQL query to execute.
	 * @param array|null $params (Not implemented) Parameters for prepared statements.
	 * @return SQLite3Result|null The result set object on success, or null on failure.
	 * @throws Exception If the SQL query is empty.
	 */
	public function queryDB($sql, $params = null)
	{
		if (empty ( $sql )) {
			throw new Exception ( "Empty Query" );
		}
		
		if ($this->databaseConn == null) {
			
			$this->databaseConn = $this->getDBConnection ();
		}
		
		if ($this->databaseConn == null) 
		{
			return null;
		}
		
		$resObj = $this->databaseConn->query ( $sql );
		

		if ($pdoResObj !== FALSE) 
		{

			$results = $resObj;
		}
		else 
		{
			
			$results = null;
		}
		

		//$this->onErrorShowDebugformation ( $pdoResObj, $sql );
		
		
		return $results;
	}
	
	/**
	 * Executes a query that returns a single value from the first row.
	 *
	 * @param string $sql The SQL query to execute.
	 * @return mixed|null The result value on success, or null on failure.
	 * @throws Exception If the SQL query is empty.
	 */
	public function queryDBSingle($sql)
	{
		if (empty ( $sql )) 
		{
			throw new Exception ( "Empty Query" );
		}
		
		if ($this->databaseConn == null) {
			$this->databaseConn = $this->getDBConnection ();
		}
		
		if ($this->databaseConn)
		{
			
			$results = $this->databaseConn->querySingle ( $sql );

			return $results;
		} 
		else 
		{
			return null;
		}
	}
	
	/**
	 * Executes a one-way query that does not return a result set (e.g., INSERT, UPDATE, DELETE).
	 *
	 * @param string $sql The SQL query to execute.
	 * @return bool|null True on success, false on failure, or null if not connected.
	 * @throws Exception If the SQL query is empty.
	 */
	public function execOnewayQuery($sql)
	{
		if (empty ( $sql ))
		{
			throw new Exception ( "Empty Query" );
		}
		
		if ($this->databaseConn == null) {
			$this->databaseConn = $this->getDBConnection ();
		}
		
		if ($this->databaseConn) {
			$execRes = $this->databaseConn->exec ( $sql );
		} else {
			return null;
		}
		
		//$this->onErrorShowDebugformation ( $execRes, $sql );
		
		return $execRes;
	}

	/**
	 * Checks if a table exists in the database.
	 *
	 * @param string $tableName The name of the table to check.
	 * @return bool True if the table exists, false otherwise.
	 */
	public function doesTableExist($tableName)
	{
		if ($tableName == null) 
		{
			return false;
		}
		
		$results = $this->queryDB ( "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = '$tableName'" );
		
		if (empty ( $results )) 
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Displays debug information on query failure if in a development environment.
	 *
	 * @param bool   $execRes The result of the query execution.
	 * @param string $sql     The SQL query that was executed.
	 * @return void
	 */
	public function onErrorShowDebugformation($execRes, $sql)
	{
		global $report;
		
		if ($execRes === false)
		{
			
			if ( $_SERVER ['REMOTE_ADDR'] == "127.0.0.1")
			{
		
				
				echo $sql . "\n<br>";
				
				echo "\nError:\n<br> ";
				print $this->databaseConn->lastErrorCode () . " : " . print_r ( $this->databaseConn->lastErrorMsg (), true );
				var_dump ( $execRes );
			}
		}
	}
	
	/**
	 * Gets the row ID of the last inserted row.
	 *
	 * @return int|null The last insert row ID, or null if not connected.
	 */
	public function getLastInsertId()
	{
		if ($this->databaseConn)
		{
			return $this->databaseConn->lastInsertRowID ();
		} 
		else 
		{
			return null;
		}
	}

	/**
	 * Closes the database connection.
	 *
	 * @return void
	 */
	public function closeDBConnection()
	{
	
		if ($this->databaseConn) 
		{
			
			$this->databaseConn->close ();
		}
		
	}
	
	/**
	 * Class destructor. Ensures the database connection is released.
	 */
	public function __destruct() {
		if (isset ( $this->databaseConn ) && $this->databaseConn != null) {
			// function not found
			$this->databaseConn = null;
		}
	}
	
	/**
	 * Gets the last error code from the database connection.
	 *
	 * @return int|null The error code, or null if not connected.
	 */
	public function lastErrorCode()
	{
		if (isset ( $this->databaseConn ) && $this->databaseConn != null) 
		{
			// function not found
			return $this->databaseConn->lastErrorCode ();
		}
	}
}

?>

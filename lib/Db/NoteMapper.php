<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/5/18
 * Time: 12:32 AM
 */

namespace OCA\OwnNotes\Db;

use DateTime;
use OCP\AppFramework\Db\DoesNotExistException;
// use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IDb;
use OCP\AppFramework\Db\Mapper;
// use OCP\IDBConnection; // IDb extends IDBConnection
use OCP\ILogger;
use PDO;

class NoteMapper extends Mapper {

	private $logger;
	private $appName;

	/**
	 * NoteMapper constructor.
	 *
	 * * Note: The default values set in the constructor param list will always be overwritten by owncloud. Owncloud will always attempt to add a 'oc_' prefix in front of custom sql table. In our case, we must explicitly specify our custom table name (*'ts_ownnotes_notes'*) and our DAO entity namespace (*'\OCA\OwnNotes\Db\Note'*) in this child constructor and pass them explicitly to the parent's super constructor (*Mapper::__construct*). This way we can prevent owncloud from changing our table names behind the scene.
	 *
	 * * Note 2: After a few experiments, specifying custom table names with custom prefix INSIDE this child constructor and then pass them to *parent::__construct* DOES NOT HELP EITHER!!!!!! Owncloud is ALWAYS going to add an ugly *'oc_'* prefix in front of my table name ANYWAY. I give up. Just rename the table name in MySQL to something starting with 'oc_' then. Fair enough. Nothing is going to be changed in this constructor though. Because here I end up explicitly specifying the table name to be '*ts_ownnotes_notes*', at run time, owncloud will change it to '*oc_ts_ownnotes_notes*' and all CRUD operations will be sent to this name as well. This is why I hate heavy-weight framework, lack of customization freedom for app developers.
	 *
	 * * Note 3: This is the final note. If table name is unspecified at compile time, owncloud will assemble a table name at run time and attempt to connect to it. The default table name assumed by owncloud is *oc_YOUR-APP-NAME_ENTITY-CLASS-NAME-IN-PLURAL*, hence, if we do not provide any table name here, the default table name owncloud is going to assume will be '*oc_ownnotes_notes*' where '*ownnotes*' is our app name specified in info.xml and '*note*' is our entity class name in lowercase.
	 *
	 * @param ILogger $logger
	 * @param $appName
	 * @param IDb $db
	 * @param string $tableName
	 * @param null|string $entityClass
	 */
	public function __construct(ILogger $logger, $appName, IDb $db = null, string $tableName = "we shall not care about the default value here because owncloud is gonna change it anyway, we must specify our table name explicitly INSIDE this child constructor. Intended value: ts_ownnotes_notes", ?string $entityClass = "we do not bother specifying a default value for our DAO entity's namespace here, same reason as above. Intended value: \OCA\OwnNotes\Db\Note") {
		parent::__construct($db, "ts_ownnotes_notes", "\OCA\OwnNotes\Db\Note");
		$this->logger = $logger;
		$this->appName = $appName;
	}

	public function log($message) {
		$this->logger->error($message, ['app' => $this->appName]);
	}

	/**
	 * @param $id
	 * @param $userId
	 * @return Note
	 */
	public function find ($id, $userId) {
		$this->log("DEBUGGING IN NoteMapper->find");
		$this->log("find operation : id => $id , userId => $userId");
		$sql = 'SELECT * From oc_ts_ownnotes_notes where id = ? AND user_id = ?';
		try {
			$result = $this->execute($sql, [$id, $userId]);
			$rows = $result->fetchAll(PDO::FETCH_BOTH);
			if ($rows === false) {
				throw new DoesNotExistException("DEBUGGING IN NoteMapper->find : Cannot find any Note satisfying the conditions");
			}
			if (count($rows) === 0 ) {
				throw new DoesNotExistException("DEBUGGING IN NoteMapper->find : Cannot find any Note satisfying the conditions");
			}
			if (count($rows) > 1 ) {
				throw new MultipleObjectsReturnedException("DEBUGGING IN NoteMapper->find : MultipleObjectsReturned!!!!");
			}
			$temp = new Note();
			foreach ($rows as $row) {
				$temp->id = $row['id'];
				$temp->title = $row['title'];
				$temp->content = $row['content'];
				$temp->userId = $row['user_id'];
				$temp->dateTime = $row['date_time'];
			}

			return $temp;
		} catch (DoesNotExistException $e) {
			$this->log("DoesNotExistException !!!! : " . $e->getMessage());
			exit(1);
		} catch (MultipleObjectsReturnedException $e) {
			$this->log("MultipleObjectsReturnedException !!!! : " . $e->getMessage());
			exit(1);
		}
	}

	/**
	 * @param $userId
	 * @return array|null
	 */
	public function findAll ($userId) {
		$sql = 'SELECT * from oc_ts_ownnotes_notes where user_id = ?';
		$result = $this->execute($sql, [$userId]);
		$rows = $result->fetchAll(PDO::FETCH_BOTH);
		if ($rows === false) {
			$this->log("DEBUGGING IN NoteMapper->findAll : fail to retrieve ALL Notes!!!!!!");
			return null;
		}
		$notes = [];
		foreach ($rows as $row) {
			$temp = new Note();
			$temp->id = $row['id'];
			$temp->title = $row['title'];
			$temp->content = $row['content'];
			$temp->userId = $row['user_id'];
			$temp->dateTime = $row['date_time'];
			$notes[] = $temp; // equivalent to array_push($notes, $temp);
		}

		return $notes;
	}

	/**
	 * overload delete method
	 *
	 * @param Note $entity
	 * @return Note
	 */
	public function remove(Note $entity) {
		$id = $entity->id;
		$title = $entity->title;
		$content = $entity->content;
		$userId = $entity->userId;
		$dateTime = $entity->dateTime;
		$sql = 'DELETE FROM oc_ts_ownnotes_notes WHERE 
			  id = ? AND 
			  title = ? AND 
			  content = ? AND 
			  user_id = ? AND 
			  date_time = ?';
		$result = $this->execute($sql, [$id, $title, $content, $userId, $dateTime]);
		if ($result->fetch() === false) {
			$this->log("DEBUGGING IN NoteMapper->remove : fail to delete specified Note!!!!!!");
			return null;
		}

		return $entity;
	}

	/**
	 * overload insert method
	 *
	 * @param Note $entity
	 * @return Note
	 */
	public function create(Note $entity) {
		$title = $entity->title;
		$content = $entity->content;
		$userId = $entity->userId;

		$this->log("DEBUGGING IN NoteMapper->create : prepare to save Note {
			title => $title;
			content => $content;
			userId => $userId;
		}");

		$d_milli = $this->getNowInSec(); // get date time in milliseconds
		$sql = 'INSERT INTO oc_ts_ownnotes_notes (title, user_id, content, date_time) VALUES (
			?, ?, ?, ?
		)';
		$this->log("DEBUGGING IN NoteMapper->create : pre insertion : {userId => $userId, dateTime => $d_milli}");
		$this->execute($sql, [$title, $userId, $content, $d_milli]);
		$sql1 = 'SELECT * From oc_ts_ownnotes_notes where date_time = ? AND user_id = ?';
		$this->log("DEBUGGING IN NoteMapper->create : post insertion : {userId => $userId, dateTime => $d_milli}");
		$result = $this->execute($sql1, [$d_milli, $userId]);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		if ($row === false) {
			$this->log("DEBUGGING IN NoteMapper->create : FAIL TO RETRIEVE SAVED NOTE!!!!!!!}");
		}
		if (count($row) === 0) {
			$this->log("DEBUGGING IN NoteMapper->create : FIND ZERO (0) RESULTS FOR SAVED NOTE!!!!!!!}");
		}

		$savedNote = new Note();
		$savedNote->id = $row['id'];
		$savedNote->title = $row['title'];
		$savedNote->content = $row['content'];
		$savedNote->userId = $row['user_id'];
		$savedNote->dateTime = $row['date_time'];

		$this->log("DEBUGGING IN NoteMapper->create : savedNote {
			id => $savedNote->id;
			title => $savedNote->title;
			content => $savedNote->content;
			userId => $savedNote->userId;
			dateTime => $savedNote->dateTime;
		}");

		return $savedNote;
	}

	/**
	 * overload update method
	 *
	 * @param Note $entity
	 * @return Note
	 */
	public function renew(Note $entity) {
		$id = $entity->id;
		$title = $entity->title;
		$content = $entity->content;
		$userId = $entity->userId;


		$sql = 'SELECT * From oc_ts_ownnotes_notes where id = ? AND user_id = ?';
		$result = $this->execute($sql, [$id, $userId]);
		if ($result->fetch() === false) {
			$this->log("DEBUGGING IN NoteMapper->renew : fail to retrieve persisted Note!!!!!!");
			return null;
		}


		$d_milli = $this->getNowInSec(); // get date time in milliseconds
		$sql1 = 'UPDATE oc_ts_ownnotes_notes SET title = ?, content = ?, date_time = ? WHERE 
			id = ? AND user_id = ?';
		$this->execute($sql1, [$title, $content, $d_milli, $id, $userId]);


		$sql2 = 'SELECT * From oc_ts_ownnotes_notes where id = ? AND user_id = ? AND date_time = ?';
		$result1 = $this->execute($sql2, [$id, $userId, $d_milli]);
		$row = $result1->fetch(PDO::FETCH_ASSOC);
		if ($row === false) {
			$this->log("DEBUGGING IN NoteMapper->renew : fail to retrieve UPDATED Note!!!!!!");
			return null;
		}
		$updatedNote = new Note();
		$updatedNote->id = $row['id'];
		$updatedNote->title = $row['title'];
		$updatedNote->content = $row['content'];
		$updatedNote->userId = $row['user_id'];
		$updatedNote->dateTime = $row['date_time'];

		return $updatedNote;
	}

	/**
	 * PHP support date time up to microseconds and mysql support DATETIME(3) which preserve up to milliseconds precision.
	 * However, owncloud DOES NOT support neither of those, it only supports the traditional DATETIME in its schema definition xml file and DATETIME only holds the precision up to seconds. Hence I use DATETIME for this app instead of DATETIME(3)
	 * @return bool|string
	 */
	private function getNowInSec(){
		$t = microtime(true);
		$micro = sprintf("%06d",($t - floor($t)) * 1000000);
		$d = new DateTime( date('Y-m-d H:i:s.'.$micro, $t) );
		$d_str = $d->format("Y-m-d H:i:s.u");
		$d_milli = substr($d_str, 0, strlen($d_str) - 3); // it is in microsecond but we want millisecond, hence remove the last three digits
		$d_milli = substr($d_milli, 0, strlen($d_milli) - 4); // unfortunately, even though mysql support DATETIME(3) since 5.7, owncloud does not support DATETIME(3) in its schema xml file (so I have to use DATETIME instead, which does not preserve milliseconds precision), as result, our php date time string will be automatically trimmed by owncloud when it performs CRUD operations for us. this is bad however we've got no choice, hence, here we remove all millisecond digits completely to preserve the consistency between the runtime values and the persisted values of our date time string.
		return $d_milli;
	}

}
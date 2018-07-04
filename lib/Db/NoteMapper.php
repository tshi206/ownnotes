<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/5/18
 * Time: 12:32 AM
 */

namespace OCA\OwnNotes\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IDb;
use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection; // IDb extends IDBConnection

class NoteMapper extends Mapper {

	public function __construct(IDb $db, string $tableName = "ownnotes_notes", ?string $entityClass = "\OCA\OwnNotes\Db\Note") {
		parent::__construct($db, $tableName, $entityClass);
	}

	public function find ($id, $userId) {
		$sql = 'SELECT * From ts_ownnotes_notes where id = ? AND user_id = ?';
		try {
			return $this->findEntity($sql, [$id, $userId]);
		} catch (DoesNotExistException $e) {
			echo $e->getMessage();
			exit(1);
		} catch (MultipleObjectsReturnedException $e) {
			echo $e->getMessage();
			exit(1);
		}
	}

	public function findAll ($userId) {
		$sql = 'SELECT * from ts_ownnotes_notes where user_id = ?';
		return $this->findEntities($sql, [$userId]);
	}

}
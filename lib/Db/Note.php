<?php /** @noinspection PhpUndefinedClassInspection */

/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/5/18
 * Time: 12:25 AM
 */

namespace OCA\OwnNotes\Db;

// The JsonSerializable interface (PHP 5 >= 5.4.0, PHP 7)
use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * Class Note
 *
 * Objects implementing JsonSerializable can customize their JSON representation when encoded with json_encode().
 *
 * @package OCA\OwnNotes\Db
 */
class Note extends Entity implements JsonSerializable {

	public $title;
	public $content;
	public $userId;
	public $dateTime;

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->content
		];
	}

	public function __toString() {
		return 'OCA\OwnNotes\Db\Note' . "{
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->content,
			'userId' => $this->userId
		}";
	}

}
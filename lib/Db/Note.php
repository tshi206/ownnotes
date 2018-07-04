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

	protected $title;
	protected $content;
	protected $userId;

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->content
		];
	}

	/**
	 * @return mixed
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param mixed $title
	 */
	public function setTitle($title): void {
		$this->title = $title;
	}

	/**
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param mixed $content
	 */
	public function setContent($content): void {
		$this->content = $content;
	}

	/**
	 * @return mixed
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * @param mixed $userId
	 */
	public function setUserId($userId): void {
		$this->userId = $userId;
	}

	/**
	 * __get, __set, __call and __callStatic are invoked when the method or property is inaccessible.
	 *
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {
		try {
			switch ($name) {
				case 'title' :
					$this->title = $value;
					break;
				case 'content' :
					$this->content = $value;
					break;
				case 'userId' :
					$this->userId = $value;
					break;
				default :
					throw new \Exception("specified field name not found in".$this);
			}
		} catch (\Exception $e) {
			echo $e;
		}
	}

	public function __toString() {
		return 'OCA\OwnNotes\Db\Note';
	}

}
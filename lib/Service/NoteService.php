<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/5/18
 * Time: 1:51 AM
 */

namespace OCA\OwnNotes\Service;

use Exception;
use OCP\AppFramework\Db\{DoesNotExistException, MultipleObjectsReturnedException};
use OCA\OwnNotes\Db\{Note, NoteMapper};
use OCP\ILogger;


/**
 * Class NoteService
 *
 * Let’s now say that our app is now on the ownCloud Marketplace, and we get a request that we should save the files in the filesystem which requires access to the filesystem.

The filesystem API is quite different from the database API and throws different exceptions, which means we need to rewrite everything in the NoteController class to use it.

This is bad, because a controller’s only responsibility should be to deal with incoming HTTP requests and return HTTP responses. If we need to change the controller because the data storage was changed the code is probably too tightly coupled. So we need to add another layer in between, a layer called Service.
 *
 * Let’s take the logic that was inside the controller and put it into a separate class inside ownnotes/lib/Service/NoteService.php
 *
 * @package OCA\OwnNotes\Service
 */
class NoteService {

	private $mapper;
	private $logger;
	private $appName;


	public function __construct(ILogger $logger, $appName, NoteMapper $mapper = null) {
		$this->mapper = $mapper;
		$this->logger = $logger;
		$this->appName = $appName;
	}

	/**
	 * @param $message
	 */
	public function log($message) {
		$this->logger->debug($message, ['app' => $this->appName]);
	}

	/**
	 * @param $userId
	 * @return array
	 */
	public function findAll($userId) {
		return $this->mapper->findAll($userId);
	}

	/**
	 * @param $e
	 * @throws NotFoundException
	 */
	private function handleException ($e) {
		if ($e instanceof DoesNotExistException || $e instanceof MultipleObjectsReturnedException) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	/**
	 * @param $id
	 * @param $userId
	 * @return \OCP\AppFramework\Db\Entity
	 * @throws NotFoundException
	 */
	public function find ($id, $userId) {
		try {
			return $this->mapper->find($id, $userId);

			// In order to be able to plug in different storage backends like files
			// for instance it is a good idea to turn storage related exceptions
			// into service related exceptions so controllers and service users
			// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
			return null;
		}
	}

	/**
	 * @param $title
	 * @param $content
	 * @param $userId
	 * @return \OCP\AppFramework\Db\Entity
	 */
	public function create ($title, $content, $userId) {
		$this->log("DEBUGGING IN NoteService->create");
		$this->log("title => $title, content => $content, userId => $userId");
		$note = new Note();
		$note->title = $title;
		$note->content = $content;
		$note->userId = $userId;
		$this->log("NoteService : GOING TO CREATE A NOTE ENTITY");
		$this->log("NEW NOTE ENTITY : {
			title => $note->title;
			content => $note->content;
			userId =>$note->userId;
		}");
		return $this->mapper->create($note);
	}

	/**
	 * @param $id
	 * @param $title
	 * @param $content
	 * @param $userId
	 * @return \OCP\AppFramework\Db\Entity
	 * @throws NotFoundException
	 */
	public function update ($id, $title, $content, $userId) {
		try {
			$this->log("DEBUGGING IN NoteService->update");
			$this->log("id : " . $id . " ; title : " . $title . " ; content : " . $content . " ; userId : " . $userId);
			$this->log("GOING TO FETCH NOTE FROM DB!!!!!");
			$this->log($this->mapper ? "mapper defined" : "MAPPER UNDEFINED!!!!!!!!!!!!");
			$note = $this->mapper->find($id, $userId);
			$this->log("TEST IM HERE!!!!!");
			/** @var Note $note
			 *
			 * PHP is weakly typed, not casting required. In this case we know from class hierarchy that our custom Note provides setters whereas its super class Entity does not. Hence, we just suppress the IDE warning here.
			 *
			 */
			$this->log($note);
			$note->title = $title;
			$note->content = $content;
			return $this->mapper->renew($note);
		} catch (Exception $e) {
			$this->handleException($e);
			return null;
		}
	}

	/**
	 * @param $id
	 * @param $userId
	 * @return \OCP\AppFramework\Db\Entity
	 * @throws NotFoundException
	 */
	public function delete ($id, $userId) {
		try {
			$note = $this->mapper->find($id, $userId);
			$this->mapper->remove($note);
			return $note;
		} catch (Exception $e) {
			$this->handleException($e);
			return null;
		}
	}

}
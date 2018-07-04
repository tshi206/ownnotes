<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/4/18
 * Time: 5:30 AM
 */

namespace OCA\OwnNotes\Controller;

use Exception;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

// we now use NoteService as an higher abstraction over Note and NoteMapper, hence the following two use statements are no longer needed
/*
use OCA\OwnNotes\Db\Note;
use OCA\OwnNotes\Db\NoteMapper;
*/

use OCA\OwnNotes\Service\NoteService;

/**
 * Class NoteController
 *
 * We now replace the old *$mapper* instance inside this class because we are using our custom *$service::NoteService* as a higher abstraction over the *$mapper::NoteMapper*
 *
 * * Note: As a result of these changes, the only reason that the controller needs to be changed is when request/response related things change.
 *
 * * Note2: If we change our backend storage, we only need to change our NoteService, NoteController remains unchanged.
 *
 * * Note3: If we want to add a new backend storage, we only need to write a new *service* class, similar to NoteService, the only change inside NoteController is to replace the *$service* with whatever service class you want. In addition, writing a new *controller* class is also simple because we can reuse our Error trait, hence, a new *controller* class can be almost identical except the class name and some field/method names might be different (but we can always use the same method names and field names as long as we rename our new *controller* class to a different name and supply it with a *service* instance field of different *service_class_type*).
 *
 * @package OCA\OwnNotes\Controller
 */
class NoteController extends Controller {

	private $service; // replace the old $mapper instance because we are using our custom $service::NoteService as a higher abstraction over the $mapper::NoteMapper
	private $userId;

	use Errors;

	/**
	 * NoteController constructor.
	 *
	 * Additionally we want to know the 'userId' of the currently logged in user. To do so, add a $UserId parameter to the constructor, which is case-sensitive. $UserId is a predefined parameter, keep it as it is.
	 *
	 * Now the mapper is defined and can be passed into the controller. You can do so by adding it as a type-hinted parameter. ownCloud will figure out how to assemble them by itself.
	 *
	 * * Note: $mapper is no longer required later on after switching to use custom NoteService which is a higher abstraction wrapping a NoteMapper instance inside. This way we can decouple our NoteController and the Storage API (MySQL in this case)
	 *
	 * * Note2: Hence, we replace the old NoteMapper parameter for the constructor with a NoteService type-hinted parameter. Again, owncloud will look up for the desired class according to the namespaces we provide in 'use' statements and assemble them by itself.
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param NoteService $service it replaces the old NoteMapper $mapper
	 * @param $UserId
	 */
	public function __construct(string $appName, IRequest $request, NoteService $service, $UserId) {
		parent::__construct($appName, $request);
		$this->service = $service;
		$this->userId = $UserId;
	}

	/**
	 * With the constructor defined, we now need to flesh out the rest of the methods, which we previously didn’t define bodies for. In index(), below, we’ll return a DataResponse object, which contains the result of using the Data Mapper’s findAll method.
	 *
	 * This method, which is supplied with the current user’s id (stored as private field instance inside NoteController), retrieves all notes created by that user. A DataResponse object is used to return generic data responses. It provides a more generic response than JSONResponse, which also works with JSON data.
	 *
	 * * Note: Now the mapper instance is wrapped inside our custom NoteService instance, this method is thus calling NoteService which in turn calls its NoteMapper inside its scope.
	 *
	 * @NoAdminRequired
	 */
	public function index() {
		return new DataResponse($this->service->findAll($this->userId));
	}

	/**
	 * This function will retrieve and return the details for a specific note. It does so by using the data mapper’s find method, which is supplied with the note’s and user’s ids. If the note cannot be retrieved, then a DataResponse is returned, which results in a 404 Not Found response.
	 *
	 * * Note: Now the mapper instance is wrapped inside our custom NoteService instance, this method is thus calling NoteService which in turn calls its NoteMapper inside its scope.
	 *
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function show($id) {
		try{
			return new DataResponse($this->service->find($id, $this->userId));
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * This method receives the note’s title and content from the route and sets them, along with the current user’s id, on a new Note entity object. The function returns the result of calling the data mapper’s insert method, which attempts to persist the Note entity in the database.
	 *
	 * * Note: Now the mapper instance is wrapped inside our custom NoteService instance, this method is thus calling NoteService which in turn calls its NoteMapper inside its scope.
	 *
	 * * Note2: We used to instantiate our Note entity here and call mapper->insert(...) directly inside this method. Now we call NoteService's create(...) method instead, which will in turn instantiate a Note entity according to the params we supply and call mapper->insert(...) internally using its own private mapper instance field.
	 *
	 * @NoAdminRequired
	 *
	 * @param string $title
	 * @param string $content
	 * @return DataResponse
	 */
	public function create($title, $content) {
		// the following codes are no longer needed as we now call NoteService instead, which will call its wrapped NoteMapper instance which will in turn perform the desired insert(...) operation together with the Note object instantiation.
//		$note = new Note();
//		$note->setTitle($title);
//		$note->setContent($content);
//		$note->setUserId($this->userId);

		return new DataResponse($this->service->create($title, $content, $this->userId));
	}

	/**
	 * Similar to the create method, it receives the note’s id, title, and content from the route. It then attempts to retrieve the note, and throws an exception if it’s unable to do so. If it can retrieve it, it then updates the title and content, and returns the response from calling the data mapper’s update function.
	 *
	 *
	 * * Note: Now the mapper instance is wrapped inside our custom NoteService instance, this method is thus calling NoteService which in turn calls its NoteMapper inside its scope.
	 *
	 * * Note2: We used to get our Note entity here, modify its fields, and then call mapper->update(...) directly inside this method. Now we call NoteService's update(...) method instead, which will in turn find and retrieve a Note entity from the DB according to the params we supply, modify it, and then call mapper->update(...) internally using its own private mapper instance field.
	 *
	 * * Note3: Here we are not directly calling NoteService's update method. We supply it as a callback to the handleNotFound function which is inherited from our Errors trait. handleNotFound function accepts any callbacks (i.e., lambdas/function literals) as long as it returns an entity (i.e., an object, like POJO), then handleNotFound will wrap the returned object from the callback into a DataResponse object and finally, handleNotFound returns a DataResponse containing the return type of the callback and additionally handleNotFound performs error handling as we used to define inside this method with try/catch block.
	 *
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $title
	 * @param string $content
	 * @return DataResponse
	 */
	public function update($id, $title, $content) {
		// the following codes are now replaced by using the handleNotFound method which we obtain from the trait Errors.php.
/*
		try {
			$note = $this->mapper->find($id, $this->userId);
		} catch (Exception $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		/** @var Note $note
		 *
		 * PHP is weakly typed, not casting required. In this case we know from class hierarchy that our custom Note provides setters whereas its super class Entity does not. Hence, we just suppress the IDE warning here.
		 *
		 */
/*
		$note->setTitle($title);
		$note->setContent($content);
		return new DataResponse($this->mapper->update($note));
*/
		return $this->handleNotFound(function () use ($id, $title, $content) {
			return $this->service->update($id, $title, $content, $this->userId);
		});
	}

	/**
	 * This, like update, will first attempt to retrieve a note, based on the supplied id, and throw an exception if it’s not able to be found. If it’s able to be found, it will then be passed to the data mapper’s delete function, which will delete the note from the database.
	 *
	 *
	 * * Note: Now the mapper instance is wrapped inside our custom NoteService instance, this method is thus calling NoteService which in turn calls its NoteMapper inside its scope.
	 *
	 * * Note2: We used to fetch our Note entity here, and then call mapper->delete(...) directly inside this method. Now we call NoteService's delete(...) method instead, which will in turn fetch a Note entity from the DB according to the params we supply, put it in memory, and then call mapper->delete(...) internally using its own private mapper instance field.
	 *
	 * * Note3: Here we are not directly calling NoteService's delete method. We supply it as a callback to the handleNotFound function which is inherited from our Errors trait (see 'use Errors;' inside this class scope). handleNotFound function accepts any callbacks (i.e., lambdas/function literals) as long as it returns an entity (i.e., an object, like POJO), then handleNotFound will wrap the returned object from the callback into a DataResponse object and finally, handleNotFound returns a DataResponse containing the return type of the callback and additionally handleNotFound performs error handling as we used to define inside this method with try/catch block.
	 *
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function destroy($id) {
		// the following codes are now replaced by handleNotFound function and NoteService's delete method.
		/*
		try {
			$note = $this->mapper->find($id, $this->userId);
		} catch (Exception $e) {
			return new DataResponse([], 404);
		}
		$this->mapper->delete($note);
		return new DataResponse($note);
		*/

		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->userId);
		});
	}

}
<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/5/18
 * Time: 2:16 AM
 */

namespace OCA\OwnNotes\Controller;

use Closure;
use OCP\AppFramework\{Http, Http\DataResponse};
use OCA\OwnNotes\Service\NotFoundException;


/**
 * Trait Errors
 *
 * Remember how we had all those ugly try/catch blocks that where checking for DoesNotExistException and simply returned a 404 response? Let’s also refactor these into a reusable class.

Specifically, we’ll use a trait, so that we can inherit methods without having to create a large inheritance hierarchy. This will be important later on when you’ve got controllers that inherit from the ApiController class instead. The trait is created in ownnotes/lib/Controller/Errors.php
 *
 * To 'extend' a trait, simply call 'use *intended_trait_name*;' WITHIN the scope of the target class BODY, for example, see how NoteController 'use Errors;' inside its class body.
 *
 * @package OCA\OwnNotes\Controller
 */
trait Errors {

	/**
	 * PHP does not allow multi-inheritance, hence here we use a trait (i.e., our Errors.php is the trait holding this method). A trait is very similar to abstract class where we can defined methods. The difference is that we can defined concrete methods in traits. Whereas, we can also define concrete methods in abstract class in PHP, in the case where we want to have 'multi-inheritance'-like behaviour we will have to use a trait, or an interface, because each class can only extend a single parent class, even if the super class is abstract.
	 *
	 * * Note: This function wraps the returned object (type Entity in our case) of its callback into a DataResponse object and return the DataResponse object together with error handling (if anything goes wrong, the DataResponse, essentially an abstraction of HTTP response packet, will contain the error message in its body and set its status to 404 in the HTTP header)
	 *
	 * @param Closure $callback
	 * @return DataResponse
	 */
	protected function handleNotFound (Closure $callback) {
		try {
			return new DataResponse($callback());
		} catch (NotFoundException $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}

}
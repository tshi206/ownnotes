<?php
namespace OCA\OwnNotes\Controller;

// The OCP namespace maps to ownCloud/core/lib/public.
use OCP\IRequest;
use OCP\AppFramework\{
    Controller,
    Http\TemplateResponse
};

/**
 * Define a new page controller
 */
class PageController extends Controller {

	public function __construct($AppName, IRequest $request){
		parent::__construct($AppName, $request);
	}

    /**
	 * The @NoAdminRequired and @NoCSRFRequired annotations in index’s docblock above turn off security checks, as they’re not necessary for this method.
	 * @NoAdminRequired
	 * @NoCSRFRequired
     */
    public function index() {
		// Renders ownnotes/templates/main.php
		return new TemplateResponse('ownnotes', 'main');
    }
}
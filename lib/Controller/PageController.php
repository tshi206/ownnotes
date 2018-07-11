<?php
namespace OCA\OwnNotes\Controller;

// The OCP namespace maps to ownCloud/core/lib/public.
use OCP\IRequest;
use OCP\AppFramework\{
    Controller,
	Http\TemplateResponse,
	Http\ContentSecurityPolicy
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
		$csp = new ContentSecurityPolicy();
		// Allows to access resources from a specific domain. Use * to allow everything from all domains.
		// here we allow ALL Javascript, images, styles, and fonts from ALL domains.
		$csp->addAllowedScriptDomain("*")->addAllowedImageDomain("*")->addAllowedStyleDomain("*")->addAllowedFontDomain("*");
		$response = new TemplateResponse('ownnotes', 'main');
		$response->setContentSecurityPolicy($csp);
		// Renders ownnotes/templates/main.php
		//return new TemplateResponse('ownnotes', 'main');
		return $response;
    }
}
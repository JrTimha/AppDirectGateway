<?php
namespace OCA\AppDirect\Controller;

use Exception;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;

use OCA\AppDirect\Config;
use OCA\AppDirect\Logger;
use OCA\AppDirect\AppDirect\ADResponseBuilder;
use OCA\AppDirect\AppDirect\ErrorCode;
use OCA\AppDirect\Service\AppDirectService;
use OCA\AppDirect\Service\EventProcessingService;
use OCA\AppDirect\Service\DBQueueService;

/**
 * This class is the main controller of this application.
 */
class ADEventController extends Controller {
	private AppDirectService $adService;
	private EventProcessingService $eventService;
  	private DBQueueService $dbQueueService;

	private $log;

	/**
	 * Created a new instance of ADEventController
	 *
	 * @param $AppName used in the parent constructor
	 * @param IRequest $request used in the parent constructor
	 * @param AppDirectService $adService the service to get event data from using the eventUrl
	 * @param EventProcessingService $eventService the service to process the event data coming from AppDirect
	 * @param DBQueueService $dbQueueService used in this context to push new jobs into the queue
	 */
	public function __construct($AppName, IRequest $request, AppDirectService $adService, EventProcessingService $eventService, DBQueueService $dbQueueService, Logger $log) {
		parent::__construct($AppName, $request);

		$this->adService = $adService;
		$this->eventService = $eventService;
    	$this->dbQueueService = $dbQueueService;

		# init logger
		$this->log = $log;
	}

	/**
	 * This function is the main and only route to access this application.
	 * This route is used by AppDirect to send event information (order, cancel, etc.).
	 * It receives the eventUrl as a query param (eventUrl). The eventUrl is later used by the $eventService to process it.
	 * @return JSONResponse including success state or error information
	 *
	 * NextCloud security annotations:
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	*/
	public function onAppDirectEvent(): JSONResponse {
		// validate auth header
		$authHeader = $this->request->getHeader('Authorization');
		if (!$this->validateAuthHeader($authHeader)) {
			$this->log->info('onAppDirectEvent', [
				'status' => 'invalid authorization using header "' . $authHeader . '"'
			]);
			return ADResponseBuilder::error(200, ErrorCode::FORBIDDEN);
		}

		// check if queryParam eventUrl is set
		$eventUrl = $this->request->getParam('eventUrl', null);
		if ($eventUrl == null) {
			$this->log->info('onAppDirectEvent', [
				'status' => 'the query param eventUrl is missing in url'
			]);
			return ADResponseBuilder::error(200, ErrorCode::UNKNOWN_ERROR);
		}

		try {
			$eventData = $this->adService->getEventData($eventUrl);
			if ($this->eventService->isSynchronousEvent($eventData)) {
				$this->eventService->processEvent($eventUrl, $eventData);

				$this->log->info('onAppDirectEvent', [
					'status' => 'event sucessfully processed',
					'eventUrl' => $eventUrl,
					'processingType' => 'synchronous'
				]);

				return ADResponseBuilder::success(200);
			} else {
				$this->dbQueueService->pushAssignment($eventUrl);

				$this->log->info('onAppDirectEvent', [
					'status' => 'event added to job queue',
					'eventUrl' => $eventUrl,
					'processingType' => 'asynchronous'
				]);

				return ADResponseBuilder::success(202);
			}
		} catch (Exception $e) {
			$this->log->info('onAppDirectEvent', [
				'status' => 'error while processing event',
				'eventURL' => $eventUrl,
				'exception' => [
					'message' => $e->getMessage(),
					'trace' => $e->getTraceAsString()
				]]);	
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, $e->getMessage());
		}
	}

	/**
	 * This function is used to validate the authorization information.
	 * Only the "AppDirect" user is allowed to perform actions via the route.
	 */
	private function validateAuthHeader($authHeader): bool {
		if ($authHeader == null) {
			return false;
		}
		if (!str_starts_with(strtolower($authHeader), "basic ")) {
			return false;
		}

		$authParts = explode(":", base64_decode(mb_substr($authHeader, 6)));
		if (count($authParts) != 2) {
			return false;
		}
		if ($authParts[0] != Config::AD_AUTH_OUT_CLIENT_ID || $authParts[1] != Config::AD_AUTH_OUT_CLIENT_SECRET) {
			return false;
		}

		return true;
	}
}

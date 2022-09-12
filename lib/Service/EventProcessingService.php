<?php
namespace OCA\AppDirect\Service;

use Exception;

use OCA\AppDirect\Config;
use OCA\AppDirect\Logger;
use OCA\AppDirect\AppDirect\ADResponseBuilder;
use OCA\AppDirect\AppDirect\EditionInfo;
use OCA\AppDirect\AppDirect\ErrorCode;
use OCA\AppDirect\NextCloud\AccountManager;

use OCP\AppFramework\Http\JSONResponse;

/**
 * This class is used to process the events coming from AppDirect.
 */
class EventProcessingService {
  // ################################################################
	// # APP DIRECT CONSTS
	// ################################################################

	public const EVT_SUBSCRIPTION_ORDER = 'SUBSCRIPTION_ORDER';
	public const EVT_SUBSCRIPTION_CHANGE = 'SUBSCRIPTION_CHANGE';
	public const EVT_SUBSCRIPTION_CANCEL = 'SUBSCRIPTION_CANCEL';
	public const EVT_SUBSCRIPTION_NOTICE = 'SUBSCRIPTION_NOTICE'; # this event is always processed synchronously

	# DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-subs-notice.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7CSubscription%2520notice%7C_____0
	public const EVT_NOT_DEACTIVATED = 'DEACTIVATED';
	public const EVT_NOT_REACTIVATED = 'REACTIVATED';
	public const EVT_NOT_CLOSED = 'CLOSED';
	public const EVT_NOT_UPCOMING_INVOICE = 'UPCOMING_INVOICE'; # currently not implemented/used

	private $adService;
	private $ncService;
	private $dbGroupService;

	private $eventUrl;

	private $log;

 	/**
	 * Creates a new instance of EventProcessingService
	 *
	 * @param AppDirectService @adService the service to communicate with AppDirect
	 * @param AccountManager $ncService the service to perform nextcloud related actions (add user, etc.)
	 * @param DBGroupService $dbGroupService the database service to store account information
	 */
	public function __construct(AppDirectService $adService, AccountManager $ncService, DBGroupService $dbGroupService, Logger $log) {
		$this->adService = $adService;
		$this->ncService = $ncService;
		$this->dbGroupService = $dbGroupService;

		# init logger
 		$this->log = $log;
	}

	// ################################################################
	// # EVENT PROCESSING FUNCTIONS
	// ################################################################

	/**
	 * The entrance function to process the events from AppDirect.
	 *
	 * @param string $eventUrl the url to get the event data from
	 * @throws
	 */
	public function processEvent(string $eventUrl, mixed $eventData = null) {
		$this->eventUrl = $eventUrl;
		if (is_null($eventData) === TRUE) {
			$eventData = $this->adService->getEventData($eventUrl);
		}

		// check flag of event
		$eventFlag = $eventData->flag;
		if ($eventFlag != null) {
			switch ($eventFlag) {
				case 'DEVELOPMENT':
					# TODO: consider DEVELOPMENT flag in PROD
					syslog(LOG_INFO, '!!!!!!FLAG DEVELOPMENT SHOULD BE CONSIDERED IN PROD!!!!!!');
					break;
				case 'STATELESS':
					$this->log->info('processEvent', [
						'status' => 'stateless event => success',
						'eventUrl' => $eventUrl
					]);
					return;
			}
		}

		// process eventData depending on eventType
        $eventType = $this->getEventType($eventData);
		switch ($eventType) {
			case self::EVT_SUBSCRIPTION_ORDER:
				$responseData = $this->onSubscriptionOrderEvent($eventData);
				$this->adService->postEventData($eventUrl, json_encode($responseData->getData()));
				break;
			case self::EVT_SUBSCRIPTION_CHANGE:
				$responseData = $this->onSubscriptionChangeEvent($eventData);
				$this->adService->postEventData($eventUrl, json_encode($responseData->getData()));
				break;
			case self::EVT_SUBSCRIPTION_CANCEL:
				$responseData = $this->onSubscriptionCancelEvent($eventData);
				$this->adService->postEventData($eventUrl, json_encode($responseData->getData()));
				break;
			case self::EVT_SUBSCRIPTION_NOTICE:
				$this->onSubscriptionNoticeEvent($eventData);
				break;
			default:
				$this->log->info('processEvent', [
					'status' => 'invalid event type "' . $eventType . '"',
					'eventUrl' => $eventUrl
				]);
				throw new Exception('error while parsing event data');
		}
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_ORDER'.
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-create-subs.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7C_____4
	 */
	public function onSubscriptionOrderEvent($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->company->name;
		$userName = $this->generateValidUsername(explode('@', $eventData->creator->email)[0]);

		$editionInfo = EditionInfo::fromEventData($eventData);
		if ($editionInfo == null) {
			$this->log->info('onSubscriptionOrderEvent', [
				'status' => 'failed parsing eventData, edition code does not exist',
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, "product does not exist");
		}

		# do next cloud related things
		$displayName = $eventData->creator->firstName . ' ' . $eventData->creator->lastName;
		$ncResp = $this->ncService->createAccountWithEmail($userName, $accountIdentifier, $eventData->creator->email, $editionInfo->getStorageAmount(), $displayName);
		if ($ncResp === false) {
			$this->log->info('onSubscriptionOrderEvent', [
				'status' => 'failed while creating account',
				'error' => $ncResp,
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error(200, ErrorCode::UNKNOWN_ERROR);
		}

		$accountIdentifier = $ncResp;
		$this->dbGroupService->createGroup($accountIdentifier, $userName, $editionInfo->getEditionCode(), $editionInfo->getUserCount(), $editionInfo->getStorageAmountTB());

		$this->log->info('onSubscriptionOrderEvent', [
			'status' => 'success',
			'eventUrl' => $this->eventUrl,
			'accountIdentifier' => $accountIdentifier
		]);

		return ADResponseBuilder::success2(200, $accountIdentifier);
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_CHANGE'.
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-change-subs.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7C_____5
	 */
	public function onSubscriptionChangeEvent($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->account->accountIdentifier;

		$editionInfo = EditionInfo::fromEventData($eventData);
		if ($editionInfo == null) {
			$this->log->info('onSubscriptionChangeEvent', [
				'status' => 'failed parsing eventData, edition code does not exist',
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' =>  $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, "product does not exist");
		}

		# do next cloud related things
		$ncResp = $this->ncService->changeAccountGroupQuota($accountIdentifier, $editionInfo->getStorageAmount());
		if ($ncResp !== TRUE) {
			$this->log->info('onSubscriptionChangeEvent', [
				'status' => 'failed changed account parameters',
				'error' => $ncResp,
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, $ncResp);
		}

		// update group entity in db
		$dbGroupEntity = $this->dbGroupService->getGroup($accountIdentifier);
		$dbGroupEntity->setEditioncode($editionInfo->getEditionCode());
		$dbGroupEntity->setUsercount($editionInfo->getUserCount());
		$dbGroupEntity->setStorage($editionInfo->getStorageAmountTB());
		$this->dbGroupService->updateGroup($dbGroupEntity);

		$this->log->info('onSubscriptionChangeEvent', [
			'status' => 'success',
			'eventUrl' => $this->eventUrl,
			'accountIdentifier' => $accountIdentifier
		]);

		return ADResponseBuilder::success(200);
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_CANCEL'.
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-cancel-subs.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7C_____6
	 */
	public function onSubscriptionCancelEvent($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->account->accountIdentifier;

		# do next cloud related things
		$ncResp = $this->ncService->deleteAccount($accountIdentifier);
		if ($ncResp !== TRUE) {
			$this->log->info('onSubscriptionCancelEvent', [
				'status' => 'failed deleting account',
				'error' => $ncResp,
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, $ncResp);
		}

		$this->dbGroupService->deleteGroup($this->dbGroupService->getGroup($accountIdentifier));

		$this->log->info('onSubscriptionCancelEvent', [
			'status' => 'success',
			'eventUrl' => $this->eventUrl,
			'accountIdentifier' => $accountIdentifier
		]);

		return ADResponseBuilder::success(200);
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_NOTICE'
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-subs-notice.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7CSubscription%2520notice%7C_____0
	 */
	public function onSubscriptionNoticeEvent($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->account->accountIdentifier;

		$noticeType = $eventData->payload->notice->type;
		switch ($noticeType) {
			case self::EVT_NOT_DEACTIVATED:
				return $this->onNotificationDeactivated($eventData);
			case self::EVT_NOT_REACTIVATED:
				return $this->onNotificationReactivated($eventData);
			case self::EVT_NOT_CLOSED:
			return $this->onNotificationClosed($eventData);
			case self::EVT_NOT_UPCOMING_INVOICE:
				# TODO: CHECK IF ANY ACTION IS NEEDED HERE
				break;
		}

		return ADResponseBuilder::success(200);
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_NOTICE' and the noticeType 'DEACTIVATED'
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-subs-notice.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7CSubscription%2520notice%7C_____0
	 */
	public function onNotificationDeactivated($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->account->accountIdentifier;

		$ncResp = $this->ncService->disableAccount($accountIdentifier);
		if ($ncResp !== TRUE) {
			$this->log->info('onNotificationDeactivated', [
				'status' => 'failed disabling account',
				'error' => $ncResp,
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, $ncResp);
		}

		$this->log->info('onNotificationDeactivated', [
			'status' => 'success',
			'eventUrl' => $this->eventUrl,
			'accountIdentifier' => $accountIdentifier
		]);

		return ADResponseBuilder::success(200);
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_NOTICE' and the noticeType 'REACTIVATED'
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-subs-notice.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7CSubscription%2520notice%7C_____0
	 */
	public function onNotificationReactivated($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->account->accountIdentifier;

		$ncResp = $this->ncService->enableAccount($accountIdentifier);
		if ($ncResp !== TRUE) {
			$this->log->info('onNotificationReactivated', [
				'status' => 'failed enabling account',
				'error' => $ncResp,
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, $ncResp);
		}

		$this->log->info('onNotificationReactivated', [
			'status' => 'success',
			'eventUrl' => $this->eventUrl,
			'accountIdentifier' => $accountIdentifier
		]);
		return ADResponseBuilder::success(200);
	}

	/**
	 * Processes events with the eventType 'SUBSCRIPTION_NOTICE' and the noticeType 'CLOSED'
	 *
	 * @param mixed $eventData the json event data from AppDirect
	 * @return JSONResponse the response to send to AppDirect
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-subs-notice.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7CSubscription%2520notice%7C_____0
	 */
	public function onNotificationClosed($eventData): JSONResponse {
		$accountIdentifier = $eventData->payload->account->accountIdentifier;

		$ncResp = $this->ncService->deleteAccount($accountIdentifier);
		if ($ncResp !== TRUE) {
			$this->log->info('onNotificationClosed', [
				'status' => 'failed deleting account',
				'error' => $ncResp,
				'eventUrl' => $this->eventUrl,
				'accountIdentifier' => $accountIdentifier
			]);
			return ADResponseBuilder::error2(200, ErrorCode::UNKNOWN_ERROR, $ncResp);
		}
		$this->dbGroupService->deleteGroup($this->dbGroupService->getGroup($accountIdentifier));

		$this->log->info('onNotificationClosed', [
			'status' => 'success',
			'eventUrl' => $this->eventUrl,
			'accountIdentifier' => $accountIdentifier
		]);
		return ADResponseBuilder::success(200);
	}

	// ################################################################
	// # HELPER FUNCTIONS
	// ################################################################

	/**
	 * @param mixed $eventData the event data object
	 * @return bool whether the given event must be processed synchronously (true) or not (false)
	 */
	public function isSynchronousEvent(mixed $eventData): bool {
		return $this->getEventType($eventData) == EventProcessingService::EVT_SUBSCRIPTION_NOTICE;
	}

	/**
	* @return string the event type of the given event
	*/
	public function getEventType($eventData) {
		return $eventData->type;
	}

	const USERNAME_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_. @';
	/**
	 * Uses a given username to remove invalid chars from it.
	 * @param string $username the username to generate a valid name
	 * @return string the valid username
	 */
	public function generateValidUsername(string $username): string {
		$newUsername = '';

		$chars = str_split($username);
		foreach ($chars as $char) {
			if (strpos(EventProcessingService::USERNAME_CHARS, $char) !== false) {
				$newUsername .= $char;
			}
		}

		return $newUsername;
	}
}

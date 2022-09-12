<?php
namespace OCA\AppDirect\Service;

use Exception;

use OCA\AppDirect\Config;
use OCA\AppDirect\Logger;
use OCA\AppDirect\AppDirect\EventGoneException;

/**
 * This service is responsible for all communications between NextCloud and Telekom Marketplace (AppDirect).
 */
class AppDirectService {
	private $accessToken;

	private $log;

	/**
	 * Creates a new instance of AppDirectService.
	 * The logger is initialized in the constructor.
	 */
	 public function __construct(Logger $log) {
		# init logger
 		$this->log = $log;
	 }

	/**
	 * Try to login to AppDirect using OAuth 2.0 client ID and secret to retreive an access token.
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#AppDistribution/Authorize-Inbound-API-requests.htm%3FTocPath%3DConfigure%2520product%2520integration%2520security%7CSeparate%2520credentials%2520authorization%2520type%7C_____2
	 */
	private function doAppDirectLogin() {
		if ($this->accessToken) {
			return;
		}

		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_session, CURLOPT_URL, Config::AD_BASE_URL . '/oauth/token');
		curl_setopt($curl_session, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded","Authorization: Basic " . base64_encode(Config::AD_AUTH_IN_CLIENT_ID . ":" . Config::AD_AUTH_IN_CLIENT_SECRET)));
		curl_setopt($curl_session, CURLOPT_POST, true);
		curl_setopt($curl_session,CURLOPT_POSTFIELDS, 'grant_type=client_credentials&scope=ROLE_APPLICATION');
		$result = curl_exec($curl_session);
		curl_close($curl_session);

		// error handling
		if ($result === false) {
			$errorMsg = '';
			if (curl_errno($curl_session)) {
    		$errorMsg = curl_error($curl_session);
			}
			$this->log->info('doAppDirectLogin', [
				'status' => 'this should not happen',
				'error' => $errorMsg
			]);
			throw new Exception('unexpected AppDirectService error: ' . $errorMsg);
		}
		$statusCode = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
		if ($statusCode !== 200) {
			$this->log->info('doAppDirectLogin', [
				'status' => 'login failed',
				'httpStatus' => $statusCode,
				'respBody' => json_decode($result)
			]);
			throw new Exception($result);
		}

		$this->log->info('doAppDirectLogin', [
			'status' => 'login successful'
		]);
		$this->accessToken = json_decode($result)->access_token;
	}

	/**
	 * This function is used to make a GET request to the eventUrl to obtain detailed information about the event.
	 * @param string $eventUrl the url to request the event from
	 * @return mixed the event data as a json if successful
	 * @throws EventGoneException when the event was already processed
	 * @throws \Exception when AppDirect returns unexpected responses
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#mng-platform/mktplc-notifs.htm%3FTocPath%3DIntegration%2520events%7C_____0
	 * Example: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-subs-ev-example.html%3FTocPath%3DIntegration%2520events%7CSubscription%2520events%7C_____3
	 */
	public function getEventData(string $eventUrl): mixed {
		$this->doAppDirectLogin();

		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_session, CURLOPT_URL, $eventUrl);
		curl_setopt($curl_session, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json", "Authorization: Bearer " . $this->accessToken));
		$result = curl_exec($curl_session);
		curl_close($curl_session);

		// error handling
		if ($result === false) {
			$errorMsg = '';
			if (curl_errno($curl_session)) {
    		$errorMsg = curl_error($curl_session);
			}
			$this->log->info('getEventData', [
				'status' => 'this should not happen',
				'error' => $errorMsg,
				'eventUrl' => $eventUrl
			]);
			throw new Exception('unexpected AppDirectService error: ' . $errorMsg);
		}
		$statusCode = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
		if ($statusCode === 410) { // GONE
			$this->log->info('getEventData', [
				'status' => 'event gone/already processed',
				'eventUrl' => $eventUrl,
				'httpStatus' => $statusCode,
				'respBody' => json_decode($result)
			]);
			throw new EventGoneException($result);
		} else if ($statusCode !== 200) {
			$this->log->info('getEventData', [
				'status' => 'get event failed',
				'eventUrl' => $eventUrl,
				'httpStatus' => $statusCode,
				'respBody' => json_decode($result)
			]);
			throw new Exception($result);
		}

		$this->log->info('getEventData', [
			'status' => 'success',
			'eventUrl' => $eventUrl
		]);
		return json_decode($result);
	}

	/**
	 * This function is used to submit the results after processing the event data from the GET request.
	 * @param string $eventUrl the url to submit the result
	 * @param string $reqBody the response message to the processed event
	 * @throws \Exception when AppDirect returns unexpected responses
	 *
	 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-notif-urls-and-responses.html%3FTocPath%3DIntegration%2520events%7C_____3
	 */
	public function postEventData(string $eventUrl, string $reqBody) {
		$this->doAppDirectLogin();

		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_session, CURLOPT_POST, TRUE);
		curl_setopt($curl_session, CURLOPT_URL, $eventUrl . "/result");
		curl_setopt($curl_session, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json", "Authorization: Bearer " . $this->accessToken));
		curl_setopt($curl_session, CURLOPT_POSTFIELDS, $reqBody);
		$result = curl_exec($curl_session);
		curl_close($curl_session);

		// error handling
		if ($result === false) {
			$errorMsg = '';
			if (curl_errno($curl_session)) {
    		$errorMsg = curl_error($curl_session);
			}
			$this->log->info('postEventData', [
				'status' => 'this should not happen',
				'error' => $errorMsg,
				'eventUrl' => $eventUrl,
				'reqBody' => json_decode($reqBody)
			]);
			throw new Exception('unexpected AppDirectService error: ' . $errorMsg);
		}
		$statusCode = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
		if ($statusCode !== 204) { // RESPONSE ON SUCCESS IS 204
			$this->log->info('postEventData', [
				'status' => 'post event failed',
				'eventUrl' => $eventUrl,
				'reqBody' => json_decode($reqBody),
				'httpStatus' => $statusCode,
				'respBody' => json_decode($result)
			]);
			throw new Exception($result);
		}

		$this->log->info('postEventData', [
			'status' => 'success',
			'eventUrl' => $eventUrl,
			'reqBody' => json_decode($reqBody)
		]);
	}

	/**
	 * This function is used monthly per contract to submit the user amount which exceeds the contracually regulated number of users.
	 * E.g.: contractual users: 5, actual users: 7 -> the userOverflow will be 2
	 * @param string $accountIdentifier the shared identifier between NextCloud and AppDirect
	 * @param int $userOverflow the amount of users exceeding the contractual user amount
	 * @throws \Exception when AppDirect returns unexpected responses
	 *
	 * DOC REFERENCE: https://help.appdirect.com/develop/Default.htm#api-sub/api-sub-meter-precon.htm%3FTocPath%3DREST%2520API%7CProduct%2520integration%7CProduct%2520integration%2520API%2520guides%7CMetered%2520usage%2520V1%7C_____3
	 */
	public function sendMeteredUsageData(string $accountIdentifier, int $userOverflow) {
		$this->doAppDirectLogin();

		$reqBody = [
			'account' => [
				'accountIdentifier' => $accountIdentifier
			],
			'items' => [
				[
					'unit' => 'ADDITIONAL_USER',
					'quantity' => $userOverflow
				]
			]
		];

		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl_session, CURLOPT_POST, TRUE);
		curl_setopt($curl_session, CURLOPT_URL, Config::AD_BASE_URL . '/api/integration/v1/billing/usage');
		curl_setopt($curl_session, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json", "Authorization: Bearer " . $this->accessToken));
		curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($reqBody));
		$result = curl_exec($curl_session);
		curl_close($curl_session);

		// error handling
		if ($result === false) {
			$errorMsg = '';
			if (curl_errno($curl_session)) {
    		$errorMsg = curl_error($curl_session);
			}
			$this->log->info('sendMeteredUsageData', [
				'status' => 'this should not happen',
				'error' => $errorMsg,
				'reqBody' => $reqBody
			]);
			throw new Exception('unexpected AppDirectService error: ' . $errorMsg);
		}
		$statusCode = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
		if ($statusCode !== 200) {
			$this->log->info('sendMeteredUsageData', [
				'status' => 'failed',
				'reqBody' => $reqBody,
				'httpStatus' => $statusCode,
				'respBody' => json_decode($result)
			]);
			throw new Exception($result);
		}

		$this->log->info('sendMeteredUsageData', [
			'status' => 'success',
			'reqBody' => $reqBody
		]);
	}
}

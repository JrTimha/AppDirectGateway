<?php
namespace OCA\AppDirect\AppDirect;

use OCP\AppFramework\Http\JSONResponse;

class ADResponseBuilder {
  public static function success(int $statusCode): JSONResponse {
    return new JSONResponse(array('success' => true), $statusCode);
  }

  public static function success2(int $statusCode, string $accountIdentifier): JSONResponse {
    return new JSONResponse(array('success' => true, 'accountIdentifier' => $accountIdentifier), $statusCode);
  }

  public static function error(int $statusCode, string $errorCode): JSONResponse {
    return new JSONResponse(array('success' => false, 'errorCode' => $errorCode), $statusCode);
	}

  public static function error2(int $statusCode, string $errorCode, string $errorMsg): JSONResponse {
    return new JSONResponse(array('success' => false, 'errorCode' => $errorCode, 'message' => $errorMsg), $statusCode);
  }
}

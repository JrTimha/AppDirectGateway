<?php
namespace OCA\AppDirect\AppDirect;

/**
 * This class stores all error codes definded in AppDirect.
 *
 * DOC REFERENCE: https://help.appdirect.com/products/Default.htm#Dev-DistributionGuide/en-error-codes.html%3FTocPath%3DIntegration%2520events%7C_____6
 */
class ErrorCode {
  public const USER_ALREADY_EXISTS = 'USER_ALREADY_EXISTS';
  public const USER_NOT_FOUND = 'USER_NOT_FOUND';
  public const ACCOUNT_NOT_FOUND = 'ACCOUNT_NOT_FOUND';
  public const MAX_USERS_REACHED = 'MAX_USERS_REACHED';
  public const UNAUTHORIZED = 'UNAUTHORIZED';
  public const OPERATION_CANCELLED = 'OPERATION_CANCELLED';
  public const CONFIGURATION_ERROR = 'CONFIGURATION_ERROR';
  public const INVALID_RESPONSE = 'INVALID_RESPONSE';
  public const PENDING = 'PENDING';
  public const FORBIDDEN = 'FORBIDDEN';
  public const BINDING_NOT_FOUND = 'BINDING_NOT_FOUND';
  public const TRANSPORT_ERROR = 'TRANSPORT_ERROR';
  public const UNKNOWN_ERROR = 'UNKNOWN_ERROR';
}

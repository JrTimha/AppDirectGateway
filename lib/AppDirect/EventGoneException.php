<?php
namespace OCA\AppDirect\AppDirect;

use Exception;

/**
 * This exception is used when processing an event but the event was already processed.
 */
class EventGoneException extends Exception {

  public function __construct(string $msg) {
    parent::__construct($msg);
  }
}

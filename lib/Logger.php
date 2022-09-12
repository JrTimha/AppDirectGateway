<?php
namespace OCA\AppDirect;

use Psr\Log\LoggerInterface;

class Logger {
  private LoggerInterface $logger;
  
  public function __construct(LoggerInterface $ncLog) {
    $this->logger = $ncLog;
  }

  public function error($msg ,array $logInfo) {
    $this->logger->error($msg . ": " . $this->array_to_string($logInfo));
  }

  public function info($msg, array $logInfo) {
    $this->logger->info($msg . ": " . $this->array_to_string($logInfo));
  }

  private function array_to_string(array $logInfo): string {
    $parts = [];

    foreach ($logInfo as $key => $value) {
      if (is_array($value)) {
        array_push($parts, $key . ': ' . $this->array_to_string($value));
      } else if (is_object($value)) {
        // converts stdClass to array
        $valueAsArray = json_decode(json_encode($value), true);
        array_push($parts, $key . ': ' . $this->array_to_string($valueAsArray));
      } else {
        array_push($parts, $key . ": " . $value);
      }
    }

    return '{' . join(', ', $parts) . '}';
  }
}

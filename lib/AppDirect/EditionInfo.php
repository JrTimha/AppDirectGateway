<?php
namespace OCA\AppDirect\AppDirect;

/**
 * This class contains information about the specific edition ordered in AppDirect
 * including the code and the amount of users and storage.
 */
class EditionInfo {
  const TERABYTE_TO_BYTE = 1024 * 1024 * 1024 * 1024;

  private string $editionCode;
  private int $userCount;
  private int $storageAmountBytes;

  /**
   * Create a new instance of EditionInfo
   *
   * @param string $editionCode the edition code (defined in EditionCode.php)
   * @param int $userCount the user amount
   * @param int $storageAmount the storage amount in bytes
   */
  public function __construct(string $editionCode, int $userCount, int $storageAmountBytes) {
    $this->editionCode = $editionCode;
    $this->userCount = $userCount;
    $this->storageAmountBytes = $storageAmountBytes;
  }

  /**
   * Static function to create a new instance of EditionInfo using the eventData from AppDirect.
   *
   * @param mixed the json object received from AppDirect
   * @return EditionInfo|null the EditionInfo instance if the editionCode is valid, otherwise null
   */
  public static function fromEventData($eventData): EditionInfo|null {
    $editionCode = $eventData->payload->order->editionCode;

    switch ($editionCode) {
			case EditionCode::EC_MCB_TRIAL:
        return new EditionInfo($editionCode, 3, 100 * (self::TERABYTE_TO_BYTE / 1024));
			case EditionCode::EC_MCB_M:
        return new EditionInfo($editionCode, 5, 1 * self::TERABYTE_TO_BYTE);
			case EditionCode::EC_MCB_L:
        return new EditionInfo($editionCode, 10, 2.5 * self::TERABYTE_TO_BYTE);
			case EditionCode::EC_MCB_XL:
        return new EditionInfo($editionCode, 20, 5 * self::TERABYTE_TO_BYTE);
			case EditionCode::EC_MCB_XXL:
        return new EditionInfo($editionCode, 30, 10 * self::TERABYTE_TO_BYTE);
			case EditionCode::EC_MCB_FLEX:
        $orderItems = $eventData->payload->order->items;
        return new EditionInfo($editionCode, self::getOrderItemQuantity($orderItems, 'USER_PER_LICENSE'), (self::getOrderItemQuantity($orderItems, 'GIGABYTE') / 1000) * self::TERABYTE_TO_BYTE);
		}

    return null;
  }

  /**
   * Static function to get ordered amount of users/storage for the MCB_FLEX
   *
   * @param mixed $orderItems the json array containing the ordered flex items
   * @param string $unit the unit to get the value of (e.g. 'USER_PER_LICENSE')
   * @return int the value of the unit
   */
  private static function getOrderItemQuantity($orderItems, string $unit): int {
		foreach ($orderItems as $item) {
			if ($item->unit == $unit) {
				return $item->quantity;
			}
		}

		return -1;
	}

  /**
   * @return string the editionCode
   */
  public function getEditionCode(): string {
    return $this->editionCode;
  }

  /**
   * @return int the user amount
   */
  public function getUserCount(): int {
    return $this->userCount;
  }

  /**
   * @return int the storage amount in bytes
   */
  public function getStorageAmount(): int {
    return $this->storageAmountBytes;
  }

  /**
   * @return int the storage amount int terabytes
   */
  public function getStorageAmountTB(): int {
    return $this->storageAmountBytes / self::TERABYTE_TO_BYTE;
  }
}

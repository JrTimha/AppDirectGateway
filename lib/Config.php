<?php
namespace OCA\AppDirect;

/**
 * This files contains configuration parameters used by this application.
 */
class Config {
  # the base url of appdirect
  public const AD_BASE_URL = "PLACEHOLDER";

  # credentials used to authorize in AppDirect
  public const AD_AUTH_IN_CLIENT_ID = "PLACEHOLDER";
  public const AD_AUTH_IN_CLIENT_SECRET = "PLACEHOLDER";

  # credentials used by AppDirect to authorize in this application
  public const AD_AUTH_OUT_CLIENT_ID = "PLACEHOLDER";
  public const AD_AUTH_OUT_CLIENT_SECRET = "PLACEHOLDER";
}

<?php

namespace Drupal\lnauth;

/**
 * Provides the LnAuthConstants class.
 */
class LnAuthConstants {

  /**
   * Provides the settings.
   *
   * @var string
   */
  const SETTINGS = 'lnauth.settings';

  /**
   * Provides the state namespace.
   */
  const STATE = 'lnauth';

  /**
   * Provides the admin permission.
   *
   * @var string
   */
  const PERMISSION_ADMIN = 'administer lnauth';

  /**
   * Provides the login route.
   *
   * @var string
   */
  const ROUTE_LOGIN = 'lnauth.login';

  /**
   * Provides the qr code route.
   *
   * @var string
   */
  const ROUTE_QRCODE = 'lnauth.qrcode';

  /**
   * Provides the callback route.
   *
   * @var string
   */
  const ROUTE_CALLBACK = 'lnauth.callback';

  /**
   * Provides the callback route.
   *
   * @var string
   */
  const ROUTE_CHECK = 'lnauth.check';

  /**
   * Provides the tag key.
   *
   * @var string
   */
  const KEY_TAG = 'tag';

  /**
   * Provides the login tag.
   *
   * @var string
   */
  const TAG_LOGIN = 'login';

  /**
   * Provides the k1 key.
   *
   * @var string
   */
  const KEY_K1 = 'k1';

  /**
   * Provides the action key.
   *
   * @var string
   */
  const KEY_ACTION = 'action';

  /**
   * Provides the sig key.
   *
   * @var string
   */
  const KEY_SIG = 'sig';

  /**
   * Provides the key key.
   *
   * @var string
   */
  const KEY_KEY = 'key';

  /**
   * Provides the display key.
   *
   * @var string
   */
  const KEY_DISPLAY = 'display';

  /**
   * Provides the view mode key.
   *
   * @var string
   */
  const KEY_VIEW_MODE = 'view_mode';

  /**
   * Provides the expiration key.
   *
   * @var string
   */
  const KEY_EXPIRATION = 'expiration';

  /**
   * Provides the frequency key.
   *
   * @var string
   */
  const KEY_FREQUENCY = 'frequency';

  /**
   * Provides the attempts key.
   *
   * @var string
   */
  const KEY_ATTEMPTS = 'attempts';

  /**
   * Provides the show button key.
   *
   * @var string
   */
  const KEY_SHOW_BUTTON = 'show_button';

  /**
   * Provides the show instructions key.
   *
   * @var string
   */
  const KEY_SHOW_INSTRUCTIONS = 'show_instructions';

  /**
   * Provides the login form key.
   *
   * @var string
   */
  const KEY_LOGIN_FORM = 'lnauth';

  /**
   * Provides the prune key.
   *
   * @var string
   */
  const KEY_PRUNE = 'prune';

  /**
   * Provides the prune responses key.
   *
   * @var string
   */
  const KEY_PRUNE_RESPONSES = 'prune_responses';

  /**
   * Provides the login action.
   *
   * @var string
   */
  const ACTION_LOGIN = 'login';

  /**
   * Provides the register action.
   *
   * @var string
   */
  const ACTION_REGISTER = 'register';

  /**
   * Provides the link action.
   *
   * @var string
   */
  const ACTION_LINK = 'link';

  /**
   * Provides the auth action.
   *
   * @var string
   */
  const ACTION_AUTH = 'auth';

  /**
   * Provides the challenges table.
   *
   * @var string
   */
  const TABLE_CHALLENGES = 'lnauth_challenges';

  /**
   * Provides the responses table.
   */
  const TABLE_RESPONSES = 'lnauth_responses';

  /**
   * Provides the new status.
   *
   * @var int
   */
  const STATUS_NEW = 0;

  /**
   * Provides the used status.
   *
   * @var int
   */
  const STATUS_USED = 1;

  /**
   * Provides the invalid status.
   *
   * @var int
   */
  const STATUS_INVALID = 0;

  /**
   * Provides the valid status.
   *
   * @var int
   */
  const STATUS_VALID = 1;

  /**
   * Provides the button display.
   */
  const DISPLAY_BUTTON = 'button';

  /**
   * Provides the qr display.
   */
  const DISPLAY_QR = 'qr';

  /**
   * Provides the hidden display.
   *
   * @var string
   */
  const VIEW_MODE_HIDDEN = 'hidden';

  /**
   * Provides the above display.
   *
   * @var string
   */
  const VIEW_MODE_DISPLAY_ABOVE = 'above';

  /**
   * Provides the below display.
   *
   * @var string
   */
  const VIEW_MODE_DISPLAY_BELOW = 'below';

  /**
   * Provides the replace display.
   *
   * @var string
   */
  const VIEW_MODE_REPLACE = 'replace';

  /**
   * Provides the destination key.
   *
   * @var string
   */
  const KEY_DESTINATION = 'lnauth_destination';

  /**
   * Provides the login step.
   *
   * @var string
   */
  const STEP_LOGIN = 'login';

  /**
   * Provides the expiration in seconds.
   *
   * @var int
   */
  const EXPIRATION = 3600;

  /**
   * Provides the provider.
   *
   * @var string
   */
  const PROVIDER = 'lnauth';

  /**
   * Provides the frequency.
   *
   * @var int
   */
  const FREQUENCY = 5000;

  /**
   * Provides the frequency.
   *
   * @var int
   */
  const ATTEMPTS = 0;

}

<?php

namespace Drupal\lnauth;

/**
 * Provides the LnAuthConstants class.
 */
class LnAuthConstants {

  /**
   * Provides the settings.
   */
  const string SETTINGS = 'lnauth.settings';

  /**
   * Provides the state namespace.
   */
  const string STATE = 'lnauth';

  /**
   * Provides the admin permission.
   */
  const string PERMISSION_ADMIN = 'administer lnauth';

  /**
   * Provides the login route.
   */
  const string ROUTE_LOGIN = 'lnauth.login';

  /**
   * Provides the qr code route.
   */
  const string ROUTE_QRCODE = 'lnauth.qrcode';

  /**
   * Provides the callback route.
   */
  const string ROUTE_CALLBACK = 'lnauth.callback';

  /**
   * Provides the callback route.
   */
  const string ROUTE_CHECK = 'lnauth.check';

  /**
   * Provides the tag key.
   */
  const string KEY_TAG = 'tag';

  /**
   * Provides the login tag.
   */
  const string TAG_LOGIN = 'login';

  /**
   * Provides the k1 key.
   */
  const string KEY_K1 = 'k1';

  /**
   * Provides the action key.
   */
  const string KEY_ACTION = 'action';

  /**
   * Provides the sig key.
   */
  const string KEY_SIG = 'sig';

  /**
   * Provides the key key.
   */
  const string KEY_KEY = 'key';

  /**
   * Provides the display key.
   */
  const string KEY_DISPLAY = 'display';

  /**
   * Provides the view mode key.
   */
  const string KEY_VIEW_MODE = 'view_mode';

  /**
   * Provides the expiration key.
   */
  const string KEY_EXPIRATION = 'expiration';

  /**
   * Provides the frequency key.
   */
  const string KEY_FREQUENCY = 'frequency';

  /**
   * Provides the attempts key.
   */
  const string KEY_ATTEMPTS = 'attempts';

  /**
   * Provides the show button key.
   */
  const string KEY_SHOW_BUTTON = 'show_button';

  /**
   * Provides the show instructions key.
   */
  const string KEY_SHOW_INSTRUCTIONS = 'show_instructions';

  /**
   * Provides the login form key.
   */
  const string KEY_LOGIN_FORM = 'lnauth';

  /**
   * Provides the prune key.
   */
  const string KEY_PRUNE = 'prune';

  /**
   * Provides the prune responses key.
   */
  const string KEY_PRUNE_RESPONSES = 'prune_responses';

  /**
   * Provides the login action.
   */
  const string ACTION_LOGIN = 'login';

  /**
   * Provides the register action.
   */
  const string ACTION_REGISTER = 'register';

  /**
   * Provides the link action.
   */
  const string ACTION_LINK = 'link';

  /**
   * Provides the auth action.
   */
  const string ACTION_AUTH = 'auth';

  /**
   * Provides the challenges table.
   */
  const string TABLE_CHALLENGES = 'lnauth_challenges';

  /**
   * Provides the responses table.
   */
  const string TABLE_RESPONSES = 'lnauth_responses';

  /**
   * Provides the new status.
   */
  const int STATUS_NEW = 0;

  /**
   * Provides the used status.
   */
  const int STATUS_USED = 1;

  /**
   * Provides the invalid status.
   */
  const int STATUS_INVALID = 0;

  /**
   * Provides the valid status.
   */
  const int STATUS_VALID = 1;

  /**
   * Provides the button display.
   */
  const string DISPLAY_BUTTON = 'button';

  /**
   * Provides the qr display.
   */
  const string DISPLAY_QR = 'qr';

  /**
   * Provides the hidden display.
   */
  const string VIEW_MODE_HIDDEN = 'hidden';

  /**
   * Provides the above display.
   */
  const string VIEW_MODE_DISPLAY_ABOVE = 'above';

  /**
   * Provides the below display.
   */
  const string VIEW_MODE_DISPLAY_BELOW = 'below';

  /**
   * Provides the replace display.
   */
  const string VIEW_MODE_REPLACE = 'replace';

  /**
   * Provides the destination key.
   */
  const string KEY_DESTINATION = 'lnauth_destination';

  /**
   * Provides the login step.
   */
  const string STEP_LOGIN = 'login';

  /**
   * Provides the expiration in seconds.
   */
  const int EXPIRATION = 3600;

  /**
   * Provides the provider.
   */
  const string PROVIDER = 'lnauth';

  /**
   * Provides the frequency.
   */
  const int FREQUENCY = 5000;

  /**
   * Provides the frequency.
   */
  const int ATTEMPTS = 0;

}

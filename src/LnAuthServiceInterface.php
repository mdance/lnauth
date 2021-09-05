<?php

namespace Drupal\lnauth;


use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Provides the LnAuthService class.
 */
interface LnAuthServiceInterface extends RenderCallbackInterface {

  /**
   * Gets the display.
   *
   * @return string
   *   A string containing the display.
   */
  public function getDisplay();

  /**
   * Gets the display options.
   *
   * @return array
   *   An array of display options.
   */
  public function getDisplayOptions();

  /**
   * Gets the view mode.
   *
   * @return string
   *   A string containing the view mode.
   */
  public function getViewMode();

  /**
   * Gets the view mode options.
   *
   * @return array
   *   An array of view modes options.
   */
  public function getViewModeOptions();

  /**
   * Gets the show button status.
   *
   * @return bool
   *   A bool indicating whether to show the button.
   */
  public function getShowButton();

  /**
   * Gets the show instructions.
   *
   * @return bool
   *   A bool indicating whether to show the instructions.
   */
  public function getShowInstructions();

  /**
   * Gets the expiration.
   *
   * @return int
   *   An int containing the expiration.
   */
  public function getExpiration();

  /**
   * Gets the frequency.
   *
   * @return int
   *   An int containing the frequency.
   */
  public function getFrequency();

  /**
   * Gets the attempts.
   *
   * @return int
   *   An int containing the attempts.
   */
  public function getAttempts();

  /**
   * Gets the prune status.
   *
   * @return int
   *   An bool containing the prune status.
   */
  public function getPrune();

  /**
   * Gets the prune responses status.
   *
   * @return int
   *   An bool containing the prune responses status.
   */
  public function getPruneResponses();

  /**
   * Saves the configuration.
   *
   * @param array $input
   *   The configuration to save.
   *
   * @return $this
   */
  public function saveConfiguration(array $input);

  /**
   * Gets the login form render array.
   *
   * @return array|mixed[]
   *   The login form render array.
   * @throws \Exception
   */
  public function renderLogin();

  /**
   * Gets the login QR code.
   *
   * @return array
   *   The login qr code render array.
   */
  public function renderQrCode();

  /**
   * Gets the callback url.
   *
   * @param string $k
   *   A string containing the k1 parameter, passed by reference.
   *
   * @return \Drupal\Core\Url
   *   The callback url.
   */
  public function getCallbackUrl(&$k1);

  /**
   * Gets the K1.
   *
   * @return string
   *    A string containing the K1.
   * @throws \Exception
   */
  public function getK1();

  /**
   * Encodes a bech32 string.
   *
   * @param string $input
   *   The string to encode.
   *
   * @return string
   *   The bech32 encoded string.
   * @throws \BitWasp\Bech32\Exception\Bech32Exception
   */
  public function bech32Encode($input);

  /**
   * Creates a challenge.
   *
   * @param string $input
   *   A string containing the challenge.
   *
   * @return \Drupal\Core\Database\StatementInterface|int|string|null
   *   The challenge record.
   * @throws \Exception
   */
  public function createChallenge($input);

  /**
   * Gets challenges.
   *
   * @param string $input
   *   A string containing the challenge.
   *
   * @return mixed
   *   An array of challenges.
   */
  public function getChallenge($input);

  /**
   * Verify a challenge.
   *
   * @param array $parameters
   *   An array of parameters.
   *
   * @return bool
   *   A boolean indicating the challenge validity.
   */
  public function verifyChallenge($parameters);

  /**
   * Gets the authname.
   *
   * @param string $input
   *   A string containing the authname.
   *
   * @return string
   *   A sha1 hashed output of the authname.
   */
  public function getAuthName($input);

  /**
   * Gets the check url.
   *
   * @param string $k1
   *   A string containing the challenge.
   *
   * @return \Drupal\Core\Url
   *   The check url.
   */
  public function getCheckUrl($k1);

  /**
   * Gets the challenge responses.
   *
   * @param int $input
   *   The challenge id.
   *
   * @return mixed
   *   An array of challenge responses.
   */
  public function getChallengeResponses($input);

  /**
   * Logs in a key user.
   *
   * @param string $input
   *   A string containing the login key.
   *
   * @return bool|\Drupal\user\UserInterface
   *   The logged in user, otherwise false.
   */
  public function loginKey($input);

  /**
   * Performs cron processing.
   */
  public function cron();

  /**
   * Prunes the challenges.
   */
  public function pruneChallenges();

  /**
   * Prunes the challenge responses.
   */
  public function pruneResponses();

  /**
   * Purges the caches.
   */
  public function purgeCache();

}

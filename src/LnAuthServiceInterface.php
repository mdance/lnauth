<?php

namespace Drupal\lnauth;


use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\Core\Url;
use Drupal\user\UserInterface;

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
  public function getDisplay(): string;

  /**
   * Gets the display options.
   *
   * @return array
   *   An array of display options.
   */
  public function getDisplayOptions(): array;

  /**
   * Gets the view mode.
   *
   * @return string
   *   A string containing the view mode.
   */
  public function getViewMode(): string;

  /**
   * Gets the view mode options.
   *
   * @return array
   *   An array of view modes options.
   */
  public function getViewModeOptions(): array;

  /**
   * Gets the show button status.
   *
   * @return bool
   *   A bool indicating whether to show the button.
   */
  public function getShowButton(): bool;

  /**
   * Gets the show instructions.
   *
   * @return bool
   *   A bool indicating whether to show the instructions.
   */
  public function getShowInstructions(): bool;

  /**
   * Gets the expiration.
   *
   * @return int
   *   An int containing the expiration.
   */
  public function getExpiration(): int;

  /**
   * Gets the frequency.
   *
   * @return int
   *   An int containing the frequency.
   */
  public function getFrequency(): int;

  /**
   * Gets the attempts.
   *
   * @return int
   *   An int containing the attempts.
   */
  public function getAttempts(): int;

  /**
   * Gets the prune status.
   *
   * @return bool
   *   An bool containing the prune status.
   */
  public function getPrune(): bool;

  /**
   * Gets the prune responses status.
   *
   * @return bool
   *   An bool containing the prune responses status.
   */
  public function getPruneResponses(): bool;

  /**
   * Saves the configuration.
   *
   * @param array $input
   *   The configuration to save.
   *
   * @return $this
   */
  public function saveConfiguration(array $input): self;

  /**
   * Gets the login form render array.
   *
   * @return array
   *   The login form render array.
   * @throws \Exception
   */
  public function renderLogin(): array;

  /**
   * Gets the login QR code.
   *
   * @return array
   *   The login qr code render array.
   */
  public function renderQrCode(): array;

  /**
   * Gets the callback url.
   *
   * @param string $k
   *   A string containing the k1 parameter, passed by reference.
   *
   * @return \Drupal\Core\Url
   *   The callback url.
   */
  public function getCallbackUrl(string &$k1): Url;

  /**
   * Gets the K1.
   *
   * @return string
   *    A string containing the K1.
   * @throws \Exception
   */
  public function getK1(): string;

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
  public function bech32Encode(string $input): string;

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
  public function createChallenge(string $input);

  /**
   * Gets challenges.
   *
   * @param string $input
   *   A string containing the challenge.
   *
   * @return array
   *   An array of challenges.
   */
  public function getChallenge(string $input): array;

  /**
   * Verify a challenge.
   *
   * @param array $parameters
   *   An array of parameters.
   *
   * @return bool
   *   A boolean indicating the challenge validity.
   */
  public function verifyChallenge(array $parameters): bool;

  /**
   * Gets the authname.
   *
   * @param string $input
   *   A string containing the authname.
   *
   * @return string
   *   A sha1 hashed output of the authname.
   */
  public function getAuthName(string $input): string;

  /**
   * Gets the check url.
   *
   * @param string $k1
   *   A string containing the challenge.
   *
   * @return \Drupal\Core\Url
   *   The check url.
   */
  public function getCheckUrl(string $k1): Url;

  /**
   * Gets the challenge responses.
   *
   * @param int $input
   *   The challenge id.
   *
   * @return array
   *   An array of challenge responses.
   */
  public function getChallengeResponses(int $input): array;

  /**
   * Logs in a key user.
   *
   * @param string $input
   *   A string containing the login key.
   *
   * @return bool|\Drupal\user\UserInterface
   *   The logged in user, otherwise false.
   */
  public function loginKey(string $input): bool|UserInterface;

  /**
   * Performs cron processing.
   */
  public function cron(): void;

  /**
   * Prunes the challenges.
   */
  public function pruneChallenges(): void;

  /**
   * Prunes the challenge responses.
   */
  public function pruneResponses(): void;

  /**
   * Purges the caches.
   */
  public function purgeCache(): void;

}

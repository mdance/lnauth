<?php

namespace Drupal\lnauth;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\externalauth\ExternalAuthInterface;
use function tkijewski\lnurl\encodeUrl;

class LnAuthService implements LnAuthServiceInterface {

  use StringTranslationTrait;

  /**
   * Provides the config.
   */
  protected Config $config;

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected StateInterface $state,
    protected Connection $connection,
    protected LoggerChannelInterface $logger,
    protected ExternalAuthInterface $externalAuth,
    protected RendererInterface $renderer,
    protected KillSwitch $killSwitch,
  ) {
    $this->config = $configFactory->getEditable(LnAuthConstants::SETTINGS);
  }

  /**
   * {@inheritDoc}
   */
  public function getDisplay(): string {
    return $this->config->get(LnAuthConstants::KEY_DISPLAY) ?? LnAuthConstants::DISPLAY_BUTTON;
  }

  /**
   * {@inheritDoc}
   */
  public function getDisplayOptions(): array {
    return [
      LnAuthConstants::DISPLAY_BUTTON => $this->t('Button'),
      LnAuthConstants::DISPLAY_QR => $this->t('QR Code'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getViewMode(): string {
    return $this->config->get(LnAuthConstants::KEY_VIEW_MODE) ?? LnAuthConstants::VIEW_MODE_DISPLAY_BELOW;
  }

  /**
   * {@inheritDoc}
   */
  public function getViewModeOptions(): array {
    return [
      LnAuthConstants::VIEW_MODE_DISPLAY_BELOW => $this->t('Below'),
      LnAuthConstants::VIEW_MODE_DISPLAY_ABOVE => $this->t('Above'),
      LnAuthConstants::VIEW_MODE_REPLACE => $this->t('Replace'),
      LnAuthConstants::VIEW_MODE_HIDDEN => $this->t('Hidden'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getShowButton(): bool {
    return $this->config->get(LnAuthConstants::KEY_SHOW_BUTTON) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getShowInstructions(): bool {
    return $this->config->get(LnAuthConstants::KEY_SHOW_INSTRUCTIONS) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getExpiration(): int {
    return $this->config->get(LnAuthConstants::KEY_EXPIRATION) ?? LnAuthConstants::EXPIRATION;
  }

  /**
   * {@inheritDoc}
   */
  public function getFrequency(): int {
    return $this->config->get(LnAuthConstants::KEY_FREQUENCY) ?? LnAuthConstants::FREQUENCY;
  }

  /**
   * {@inheritDoc}
   */
  public function getAttempts(): int {
    return $this->config->get(LnAuthConstants::KEY_ATTEMPTS) ?? LnAuthConstants::ATTEMPTS;
  }

  /**
   * {@inheritDoc}
   */
  public function getPrune(): bool {
    return $this->config->get(LnAuthConstants::KEY_PRUNE) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getPruneResponses(): bool {
    return $this->config->get(LnAuthConstants::KEY_PRUNE_RESPONSES) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function saveConfiguration(array $input): self {
    $keys = [
      LnAuthConstants::KEY_DISPLAY,
      LnAuthConstants::KEY_VIEW_MODE,
      LnAuthConstants::KEY_SHOW_INSTRUCTIONS,
      LnAuthConstants::KEY_EXPIRATION,
      LnAuthConstants::KEY_FREQUENCY,
      LnAuthConstants::KEY_ATTEMPTS,
      LnAuthConstants::KEY_PRUNE,
      LnAuthConstants::KEY_PRUNE_RESPONSES,
    ];

    $save = FALSE;

    foreach ($keys as $key) {
      if (isset($input[$key])) {
        $this->config->set($key, $input[$key]);
        $save = TRUE;
      }
    }

    if ($save) {
      $this->config->save();
    }

    $keys = [
    ];

    $state = $this->state->get(LnAuthConstants::STATE, []);

    $save = FALSE;

    foreach ($keys as $key) {
      if (isset($input[$key])) {
        $state[$key] = $input[$key];
        $save = TRUE;
      }
    }

    if ($save) {
      $this->state->set(LnAuthConstants::STATE, $state);
    }

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function renderLogin(): array {
    $output = [];

    $view_mode = $this->getViewMode();

    if ($view_mode != 'hidden') {
      $display = $this->getDisplay();

      if ($display == LnAuthConstants::DISPLAY_BUTTON) {
        $text = <<<EOF
        <svg class="mr-3" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="">
          <path d="M19 10.1907L8.48754 21L12.6726 12.7423H5L14.6157 3L11.5267 10.2835L19 10.1907Z" fill="black"></path>
        </svg>
EOF;

        $text = [
          '#type' => 'html_tag',
          '#tag' => 'svg',
          '#attributes' => [
            'class' => [
              'lightning-bolt',
            ],
            'width' => 24,
            'height' => 24,
            'viewBox' => '0 0 24 24',
            'fill' => 'none',
            'xmlns' => 'http://www.w3.org/2000/svg',
          ],
          'path' => [
            '#type' => 'html_tag',
            '#tag' => 'path',
            '#attributes' => [
              'd' => 'M19 10.1907L8.48754 21L12.6726 12.7423H5L14.6157 3L11.5267 10.2835L19 10.1907Z',
              'fill' => 'black',
            ],
          ],
        ];

        $text = $this->renderer->render($text);
        $text .= $this->t('Login with Lightning');

        $text = Markup::create($text);
        $url = Url::fromRoute(LnAuthConstants::ROUTE_LOGIN);

        $dialog_options = [
          'width' => '25%',
        ];

        $dialog_options = Json::encode($dialog_options);

        $options = [
          'attributes' => [
            'class' => [
              'btn-lightning',
              'use-ajax',
            ],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => $dialog_options,
          ],
        ];

        $url->setOptions($options);

        $output = Link::fromTextAndUrl($text, $url);
        $output = $output->toRenderable();
        $output['#attached']['library'][] = 'lnauth/default';
      }
      else {
        $this->killSwitch->trigger();

        $output = $this->renderQrCode();
      }
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function renderQrCode(): array {
    $attributes = [];

    $data = $this->getCallbackUrl($k1)->toString();
    $data = $this->bech32Encode($data);

    $route_parameters = [];

    $options = [
      'query' => [
        'data' => $data,
      ],
    ];

    $src = Url::fromRoute(LnAuthConstants::ROUTE_QRCODE, $route_parameters, $options)->toString();

    $attributes['src'] = $src;

    $frequency = $this->getFrequency();
    $attempts = $this->getAttempts();

    $check_url = $this->getCheckUrl($k1);
    $check_url = $check_url->toString();

    $output = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'container-lnauth',
        ],
      ],
      '#attached' => [
        'drupalSettings' => [
          'lnauth' => [
            [
              LnAuthConstants::KEY_K1 => $k1,
              LnAuthConstants::KEY_FREQUENCY => $frequency,
              LnAuthConstants::KEY_ATTEMPTS => $attempts,
              'url' => $check_url,
            ]
          ],
        ],
        'library' => [
          'lnauth/default',
        ]
      ],
    ];

    if ($this->getShowInstructions()) {
      $args = [];

      $text = t('lnauth compatible wallet');
      $url = Url::fromUri('https://github.com/fiatjaf/awesome-lnurl#wallets');

      $link = Link::fromTextAndUrl($text, $url)->toString();

      $args['@link'] = $link;

      $output['instructions'] = [
        '#markup' => t('Please scan the following qr code with a @link', $args),
      ];
    }

    $output['qrcode'] = [
      '#type' => 'html_tag',
      '#tag' => 'img',
      '#attributes' => $attributes,
    ];

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getCallbackUrl(&$k1): Url {
    $route_name = LnAuthConstants::ROUTE_CALLBACK;

    $options = [
      'query' => [],
      'absolute' => TRUE,
    ];

    $query = &$options['query'];

    $query[LnAuthConstants::KEY_ACTION] = LnAuthConstants::ACTION_LOGIN;

    $k1 = $this->getK1();
    $this->createChallenge($k1);

    $query[LnAuthConstants::KEY_K1] = $k1;
    $query[LnAuthConstants::KEY_TAG] = LnAuthConstants::TAG_LOGIN;
    $query['XDEBUG_SESSION_START'] = 'phpstorm';

    return new Url($route_name, [], $options);
  }

  /**
   * {@inheritDoc}
   */
  public function getK1(): string {
    if (extension_loaded('openssl')) {
      $output = openssl_random_pseudo_bytes(32);
    } else {
      $output = random_bytes(32);
    }

    return bin2hex($output);
  }

  /**
   * {@inheritDoc}
   */
  public function bech32Encode($input): string {
    return encodeUrl($input);
  }

  /**
   * {@inheritDoc}
   */
  public function createChallenge($input) {
    $query = $this->connection->insert(LnAuthConstants::TABLE_CHALLENGES);

    $fields = [];

    $fields['created'] = time();
    $fields['challenge'] = $input;
    $fields['status'] = LnAuthConstants::STATUS_NEW;

    $query->fields($fields);

    return $query->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function getChallenge($input) {
    $query = $this->connection->select(LnAuthConstants::TABLE_CHALLENGES, 'b');

    $query->fields('b');
    $query->condition('challenge', $input);

    $output = $query->execute();
    return $output->fetchAll();
  }

  /**
   * {@inheritDoc}
   */
  public function verifyChallenge($parameters) {
    $output = FALSE;

    //$action = $parameters['LnAuthConstants::KEY_ACTION'] ?? '';
    //$tag = $parameters[LnAuthConstants::KEY_TAG] ?? '';
    $k1 = $parameters[LnAuthConstants::KEY_K1] ?? '';

    $challenges = $this->getChallenge($k1);

    if ($challenges) {
      $now = time();
      $expiration = $this->getExpiration();

      foreach ($challenges as $challenge) {
        $challenge = (array) $challenge;

        $status = $challenge['status'];

        if ($status != LnAuthConstants::STATUS_NEW) {
          // Challenge has been used
          continue;
        }

        $created = $challenge['created'];

        if ($now - $created > $expiration) {
          // Challenge has expired
          continue;
        }

        $sig = $parameters[LnAuthConstants::KEY_SIG] ?? '';
        $key = $parameters[LnAuthConstants::KEY_KEY] ?? '';

        if ($k1 && $sig && $key) {
          // https://github.com/fiatjaf/lnurl-rfc/blob/master/lnurl-auth.md
          // sig=<hex(sign(utf8ToBytes(k1), linkingPrivKey))>
          // key=<hex(linkingKey)>
          $ecdsa = new BitcoinECDSA();

          $output = $ecdsa->checkDerSignature($key, $sig, $k1);

          if ($output) {
            try {
              $query = $this->connection->upsert(LnAuthConstants::TABLE_CHALLENGES)
                ->key('id');

              $challenge['updated'] = $now;
              $challenge['status'] = LnAuthConstants::STATUS_USED;

              $fields = array_keys($challenge);
              $values = array_values($challenge);

              $query->fields($fields, $values);

              $query->execute();
            } catch (\Exception $e) {
              $message = 'An error occurred updating challenge @id';

              $args = [];

              $args['@id'] = $challenge['id'];

              $this->logger->error($message, $args);
            }

            try {
              $authname = $this->getAuthName($key);

              $account_data = [];

              $authmap_data = [
                'key' => $key,
              ];

              $this->externalAuth->loginRegister($authname, LnAuthConstants::PROVIDER, $account_data, $authmap_data);
            } catch (\Exception $e) {
              $message = 'An error occurred registering an account for key @key';

              $args = [];

              $args['@key'] = $key;

              $this->logger->error($message, $args);
            }
          }

          try {
            $query = $this->connection->insert(LnAuthConstants::TABLE_RESPONSES);

            $fields = [];

            $fields['challenge_id'] = $challenge['id'];
            $fields['created'] = time();
            $fields['signature'] = $sig;
            $fields['key'] = $key;
            $fields['status'] = $output;

            $query->fields($fields);

            $query->execute();
          } catch (\Exception $e) {
            $message = 'An error occurred adding the challenge @id response for signature: @sig key: @key';

            $args = [];

            $args['@id'] = $challenge['id'];
            $args['@sig'] = $sig;
            $args['@key'] = $key;

            $this->logger->error($message, $args);
          }
        }
      }
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getAuthName($input): string {
    return hash('sha1', $input);
  }

  /**
   * {@inheritDoc}
   */
  public function getCheckUrl($k1): Url {
    $route_name = LnAuthConstants::ROUTE_CHECK;

    $options = [
      'query' => [],
      'absolute' => TRUE,
    ];

    $query = &$options['query'];

    $query[LnAuthConstants::KEY_K1] = $k1;

    $output = new Url($route_name, [], $options);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getChallengeResponses($input) {
    $query = $this->connection->select(LnAuthConstants::TABLE_RESPONSES, 'b');

    $query->fields('b');
    $query->condition('challenge_id', $input);

    $output = $query->execute();
    return $output->fetchAll();
  }

  /**
   * {@inheritDoc}
   */
  public function loginKey($input) {
    $authname = $this->getAuthname($input);

    return $this->externalAuth->login($authname, LnAuthConstants::PROVIDER);
  }

  /**
   * Perform the login form pre render.
   *
   * Responsible for disabling form children, and remove prefix/suffix values.
   */
  public static function loginFormPreRender($form) {
    $children = Element::children($form);

    foreach ($children as $child) {
      if ($child == LnAuthConstants::KEY_LOGIN_FORM) {
        continue;
      }

      $form['#prefix'] = '';
      $form['#suffix'] = '';
      $form[$child]['#access'] = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function cron(): void {
    if ($this->getPrune()) {
      $this->pruneChallenges();
    }

    if ($this->getPruneResponses()) {
      $this->pruneResponses();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function pruneChallenges(): void {
    $query = $this->connection->delete(LnAuthConstants::TABLE_CHALLENGES);

    $expiration = $this->getExpiration();
    $value = time() + $expiration;

    $query->condition('created', $value, '<=');
    $query->condition('status', LnAuthConstants::STATUS_NEW);

    $query->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function pruneResponses(): void {
    $keys = [];

    $query = $this->connection->select('authmap', 'a');

    $query->fields('a', ['data']);
    $query->condition('provider', LnAuthConstants::PROVIDER);

    $results = $query->execute();

    foreach ($results as $result) {
      $data = unserialize($result->data);

      if (isset($data['key'])) {
        $keys[] = $data['key'];
      }
    }

    $total = count($keys);

    if ($total) {
      $query = $this->connection->delete(LnAuthConstants::TABLE_RESPONSES);

      $query->condition('key', $keys, 'IN');

      $query->execute();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function purgeCache(): void {
    $query = $this->connection->delete('cache_page');

    $query->condition('cid', '%user/login%', 'LIKE');

    $query->execute();
  }

}

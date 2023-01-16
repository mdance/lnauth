<?php

namespace Drupal\lnauth;

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
use Drupal\Component\Serialization\Json;
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

class LnAddressService implements LnAddressServiceInterface {

  use StringTranslationTrait;

  /**
   * Provides the config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Provides the config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Provides the state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Provides the database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Provides the logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Provides the external authentication service.
   *
   * @var \Drupal\externalauth\ExternalAuthInterface
   */
  protected $externalAuth;

  /**
   * Provides the renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    StateInterface $state,
    Connection $connection,
    LoggerChannelInterface $logger,
    ExternalAuthInterface $external_auth,
    RendererInterface $renderer,
    KillSwitch $kill_switch
  ) {
    $this->configFactory = $config_factory;
    $this->config = $config_factory->getEditable(LnAddressConstants::SETTINGS);
    $this->state = $state;
    $this->connection = $connection;
    $this->logger = $logger;
    $this->externalAuth = $external_auth;
    $this->renderer = $renderer;
    $this->killSwitch = $kill_switch;
  }

  /**
   * {@inheritDoc}
   */
  public function getDisplay() {
    return $this->config->get(LnAddressConstants::KEY_DISPLAY) ?? LnAddressConstants::DISPLAY_BUTTON;
  }

  /**
   * {@inheritDoc}
   */
  public function getDisplayOptions() {
    $output = [
      LnAddressConstants::DISPLAY_BUTTON => $this->t('Button'),
      LnAddressConstants::DISPLAY_QR => $this->t('QR Code'),
    ];

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getViewMode() {
    return $this->config->get(LnAddressConstants::KEY_VIEW_MODE) ?? LnAddressConstants::VIEW_MODE_DISPLAY_BELOW;
  }

  /**
   * {@inheritDoc}
   */
  public function getViewModeOptions() {
    $output = [
      LnAddressConstants::VIEW_MODE_DISPLAY_BELOW => $this->t('Below'),
      LnAddressConstants::VIEW_MODE_DISPLAY_ABOVE => $this->t('Above'),
      LnAddressConstants::VIEW_MODE_REPLACE => $this->t('Replace'),
      LnAddressConstants::VIEW_MODE_HIDDEN => $this->t('Hidden'),
    ];

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getShowButton() {
    return $this->config->get(LnAddressConstants::KEY_SHOW_BUTTON) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getShowInstructions() {
    return $this->config->get(LnAddressConstants::KEY_SHOW_INSTRUCTIONS) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getExpiration() {
    return $this->config->get(LnAddressConstants::KEY_EXPIRATION) ?? LnAddressConstants::EXPIRATION;
  }

  /**
   * {@inheritDoc}
   */
  public function getFrequency() {
    return $this->config->get(LnAddressConstants::KEY_FREQUENCY) ?? LnAddressConstants::FREQUENCY;
  }

  /**
   * {@inheritDoc}
   */
  public function getAttempts() {
    return $this->config->get(LnAddressConstants::KEY_ATTEMPTS) ?? LnAddressConstants::ATTEMPTS;
  }

  /**
   * {@inheritDoc}
   */
  public function getPrune() {
    return $this->config->get(LnAddressConstants::KEY_PRUNE) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getPruneResponses() {
    return $this->config->get(LnAddressConstants::KEY_PRUNE_RESPONSES) ?? TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function saveConfiguration(array $input) {
    $keys = [
      LnAddressConstants::KEY_DISPLAY,
      LnAddressConstants::KEY_VIEW_MODE,
      LnAddressConstants::KEY_SHOW_INSTRUCTIONS,
      LnAddressConstants::KEY_EXPIRATION,
      LnAddressConstants::KEY_FREQUENCY,
      LnAddressConstants::KEY_ATTEMPTS,
      LnAddressConstants::KEY_PRUNE,
      LnAddressConstants::KEY_PRUNE_RESPONSES,
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

    $state = $this->state->get(LnAddressConstants::STATE, []);

    $save = FALSE;

    foreach ($keys as $key) {
      if (isset($input[$key])) {
        $state[$key] = $input[$key];
        $save = TRUE;
      }
    }

    if ($save) {
      $this->state->set(LnAddressConstants::STATE, $state);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function renderLogin() {
    $output = [];

    $view_mode = $this->getViewMode();

    if ($view_mode != 'hidden') {
      $display = $this->getDisplay();

      if ($display == LnAddressConstants::DISPLAY_BUTTON) {
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
        $url = Url::fromRoute(LnAddressConstants::ROUTE_LOGIN);

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
  public function renderQrCode() {
    $attributes = [];

    $data = $this->getCallbackUrl($k1)->toString();
    $data = $this->bech32Encode($data);

    $route_parameters = [];

    $options = [
      'query' => [
        'data' => $data,
      ],
    ];

    $src = Url::fromRoute(LnAddressConstants::ROUTE_QRCODE, $route_parameters, $options)->toString();

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
              LnAddressConstants::KEY_K1 => $k1,
              LnAddressConstants::KEY_FREQUENCY => $frequency,
              LnAddressConstants::KEY_ATTEMPTS => $attempts,
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
  public function getCallbackUrl(&$k1) {
    $route_name = LnAddressConstants::ROUTE_CALLBACK;

    $options = [
      'query' => [],
      'absolute' => TRUE,
    ];

    $query = &$options['query'];

    $query[LnAddressConstants::KEY_ACTION] = LnAddressConstants::ACTION_LOGIN;

    $k1 = $this->getK1();
    $this->createChallenge($k1);

    $query[LnAddressConstants::KEY_K1] = $k1;
    $query[LnAddressConstants::KEY_TAG] = LnAddressConstants::TAG_LOGIN;
    $query['XDEBUG_SESSION_START'] = 'phpstorm';

    $output = new Url($route_name, [], $options);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getK1() {
    if (extension_loaded('openssl')) {
      $output = openssl_random_pseudo_bytes(32);
    } else {
      $output = random_bytes(32);
    }

    $output = bin2hex($output);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function bech32Encode($input) {
    $output = encodeUrl($input);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function createChallenge($input) {
    $query = $this->connection->insert(LnAddressConstants::TABLE_CHALLENGES);

    $fields = [];

    $fields['created'] = time();
    $fields['challenge'] = $input;
    $fields['status'] = LnAddressConstants::STATUS_NEW;

    $query->fields($fields);

    $output = $query->execute();

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getChallenge($input) {
    $query = $this->connection->select(LnAddressConstants::TABLE_CHALLENGES, 'b');

    $query->fields('b');
    $query->condition('challenge', $input);

    $output = $query->execute();
    $output = $output->fetchAll();

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function verifyChallenge($parameters) {
    $output = FALSE;

    //$action = $parameters['LnAuthConstants::KEY_ACTION'] ?? '';
    //$tag = $parameters[LnAuthConstants::KEY_TAG] ?? '';
    $k1 = $parameters[LnAddressConstants::KEY_K1] ?? '';

    $challenges = $this->getChallenge($k1);

    if ($challenges) {
      $now = time();
      $expiration = $this->getExpiration();

      foreach ($challenges as $challenge) {
        $challenge = (array) $challenge;

        $status = $challenge['status'];

        if ($status != LnAddressConstants::STATUS_NEW) {
          // Challenge has been used
          continue;
        }

        $created = $challenge['created'];

        if ($now - $created > $expiration) {
          // Challenge has expired
          continue;
        }

        $sig = $parameters[LnAddressConstants::KEY_SIG] ?? '';
        $key = $parameters[LnAddressConstants::KEY_KEY] ?? '';

        if ($k1 && $sig && $key) {
          // https://github.com/fiatjaf/lnurl-rfc/blob/master/lnurl-auth.md
          // sig=<hex(sign(utf8ToBytes(k1), linkingPrivKey))>
          // key=<hex(linkingKey)>
          $ecdsa = new BitcoinECDSA();

          $output = $ecdsa->checkDerSignature($key, $sig, $k1);

          if ($output) {
            try {
              $query = $this->connection->upsert(LnAddressConstants::TABLE_CHALLENGES)
                ->key('id');

              $challenge['updated'] = $now;
              $challenge['status'] = LnAddressConstants::STATUS_USED;

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

              $this->externalAuth->loginRegister($authname, LnAddressConstants::PROVIDER, $account_data, $authmap_data);
            } catch (\Exception $e) {
              $message = 'An error occurred registering an account for key @key';

              $args = [];

              $args['@key'] = $key;

              $this->logger->error($message, $args);
            }
          }

          try {
            $query = $this->connection->insert(LnAddressConstants::TABLE_RESPONSES);

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
  public function getAuthName($input) {
    return hash('sha1', $input);
  }

  /**
   * {@inheritDoc}
   */
  public function getCheckUrl($k1) {
    $route_name = LnAddressConstants::ROUTE_CHECK;

    $options = [
      'query' => [],
      'absolute' => TRUE,
    ];

    $query = &$options['query'];

    $query[LnAddressConstants::KEY_K1] = $k1;

    $output = new Url($route_name, [], $options);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getChallengeResponses($input) {
    $query = $this->connection->select(LnAddressConstants::TABLE_RESPONSES, 'b');

    $query->fields('b');
    $query->condition('challenge_id', $input);

    $output = $query->execute();
    $output = $output->fetchAll();

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function loginKey($input) {
    $authname = $this->getAuthname($input);

    return $this->externalAuth->login($authname, LnAddressConstants::PROVIDER);
  }

  /**
   * Perform the login form pre render.
   *
   * Responsible for disabling form children, and remove prefix/suffix values.
   */
  public static function loginFormPreRender($form) {
    $children = Element::children($form);

    foreach ($children as $child) {
      if ($child == LnAddressConstants::KEY_LOGIN_FORM) {
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
  public function cron() {
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
  public function pruneChallenges() {
    $query = $this->connection->delete(LnAddressConstants::TABLE_CHALLENGES);

    $expiration = $this->getExpiration();
    $value = time() + $expiration;

    $query->condition('created', $value, '<=');
    $query->condition('status', LnAddressConstants::STATUS_NEW);

    $query->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function pruneResponses() {
    $keys = [];

    $query = $this->connection->select('authmap', 'a');

    $query->fields('a', ['data']);
    $query->condition('provider', LnAddressConstants::PROVIDER);

    $results = $query->execute();

    foreach ($results as $result) {
      $data = unserialize($result->data);

      if (isset($data['key'])) {
        $keys[] = $data['key'];
      }
    }

    $total = count($keys);

    if ($total) {
      $query = $this->connection->delete(LnAddressConstants::TABLE_RESPONSES);

      $query->condition('key', $keys, 'IN');

      $query->execute();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function purgeCache() {
    $query = $this->connection->delete('cache_page');

    $query->condition('cid', '%user/login%', 'LIKE');

    $query->execute();
  }

}

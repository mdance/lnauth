<?php

namespace Drupal\lnauth\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lnauth\LnAuthConstants;
use Drupal\lnauth\LnAuthServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the LnAuthController controller.
 */
class LnAuthController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Provides the module service.
   *
   * @var \Drupal\lnauth\LnAuthServiceInterface
   */
  protected $service;

  /**
   * Provides the constructor.
   */
  public function __construct(
    LnAuthServiceInterface $service
  ) {
    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lnauth')
    );
  }

  /**
   * Provides the login route.
   *
   * @return array
   */
  public function login() {
    $output = $this->service->renderQrCode();

    return $output;
  }

  /**
   * Provides the authentication callback.
   */
  public function callback(Request $request) {
    $output = new JsonResponse();

    $parameters = $request->query->all();

    $error = TRUE;

    try {
      $result = $this->service->verifyChallenge($parameters);

      if ($result) {
        $error = FALSE;
      }
    } catch (\Exception $e) {
      watchdog_exception('lnauth', $e);
    }

    if ($error) {
      $output->setStatusCode(500);

      $data = [
        'status' => 'ERROR',
        'reason' => 'An error has occurred.',
      ];

      $output->setData($data);
    } else {
      $output->setStatusCode(200);

      $data = [
        'status' => 'OK',
      ];

      $output->setData($data);
    }

    return $output;
  }

  /**
   * Provides the check callback.
   */
  public function check(Request $request) {
    $output = new JsonResponse();

    $error = FALSE;
    $authenticated = FALSE;

    $parameters = $request->query->all();

    $k1 = $parameters[LnAuthConstants::KEY_K1] ?? '';

    if (empty($k1)) {
      $error = TRUE;
    } else {
      try {
        $challenges = $this->service->getChallenge($k1);

        foreach ($challenges as $challenge) {
          $challenge = (array)$challenge;

          if ($challenge['status'] == LnAuthConstants::STATUS_USED) {
            $id = $challenge['id'];

            $responses = $this->service->getChallengeResponses($id);

            foreach ($responses as $response) {
              $response = (array)$response;

              if ($response['status'] == LnAuthConstants::STATUS_VALID) {
                $account = $this->service->loginKey($response['key']);

                if ($account) {
                  $authenticated = TRUE;
                }
              }
            }
          }
        }
      } catch (\Exception $e) {
        $error = TRUE;
      }
    }

    if ($error) {
      $output->setStatusCode(500);
    } else {
      $output->setStatusCode(200);
    }

    $data = [
      'error' => $error,
      'authenticated' => $authenticated,
    ];

    $output->setData($data);

    return $output;
  }

}

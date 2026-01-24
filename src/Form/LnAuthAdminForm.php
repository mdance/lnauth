<?php

namespace Drupal\lnauth\Form;

use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\lnauth\LnAuthConstants;
use Drupal\lnauth\LnAuthServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the AdminForm class.
 */
class LnAuthAdminForm extends ConfigFormBase {

  /**
   * Provides the constructor method.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\lnauth\LnAuthServiceInterface $service
   *   The module service.
   */
  public function __construct(
    protected $configFactory,
    protected TypedConfigManagerInterface $typedConfigManager,
    protected LnAuthServiceInterface $service,
  ) {
    parent::__construct($configFactory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lnauth_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [LnAuthConstants::SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $key = LnAuthConstants::KEY_DISPLAY;

    $default_value = $this->service->getDisplay();
    $options = $this->service->getDisplayOptions();

    $form[$key] = [
      '#type' => 'radios',
      '#title' => $this->t('Login Button Display'),
      '#options' => $options,
      '#default_value' => $default_value,
    ];

    $key = LnAuthConstants::KEY_VIEW_MODE;

    $default_value = $this->service->getViewMode();
    $options = $this->service->getViewModeOptions();

    $form[$key] = [
      '#type' => 'radios',
      '#title' => $this->t('View Mode'),
      '#default_value' => $default_value,
      '#options' => $options,
    ];

    $key = LnAuthConstants::KEY_SHOW_INSTRUCTIONS;

    $default_value = $this->service->getShowInstructions();

    $form[$key] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show instructions'),
      '#default_value' => $default_value,
    ];

    $key = LnAuthConstants::KEY_EXPIRATION;

    $default_value = $this->service->getExpiration();

    $form[$key] = [
      '#type' => 'number',
      '#title' => $this->t('Challenge Expiration'),
      '#default_value' => $default_value,
      '#min' => 60,
    ];

    $key = LnAuthConstants::KEY_FREQUENCY;

    $default_value = $this->service->getFrequency();

    $form[$key] = [
      '#type' => 'number',
      '#title' => $this->t('Login Check Frequency'),
      '#default_value' => $default_value,
      '#min' => 1000,
    ];

    $key = LnAuthConstants::KEY_ATTEMPTS;

    $default_value = $this->service->getAttempts();

    $form[$key] = [
      '#type' => 'number',
      '#title' => $this->t('Login Check Attempts'),
      '#default_value' => $default_value,
      '#min' => 0,
    ];

    $key = LnAuthConstants::KEY_PRUNE;

    $default_value = $this->service->getPrune();

    $form[$key] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prune challenges'),
      '#default_value' => $default_value,
    ];

    $key = LnAuthConstants::KEY_PRUNE_RESPONSES;

    $default_value = $this->service->getPruneResponses();

    $form[$key] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prune responses'),
      '#default_value' => $default_value,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->cleanValues()->getValues();

    $this->service->saveConfiguration($values);
    $this->service->purgeCache();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('lnauth'),
    );
  }

}

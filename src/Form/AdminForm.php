<?php

namespace Drupal\lnauth\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\lnauth\LnAuthConstants;
use Drupal\lnauth\LnAuthServiceInterface;
use Drupal\lnauth\LnAuthServiceTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the AdminForm class.
 */
class AdminForm extends ConfigFormBase {

  use LnAuthServiceTrait;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LnAuthServiceInterface $lnauth
  ) {
    parent::__construct($config_factory);

    $this->lnauth = $lnauth;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('lnauth')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lnauth_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [LnAuthConstants::SETTINGS];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $key = LnAuthConstants::KEY_DISPLAY;

    $default_value = $this->lnauth()->getDisplay();
    $options = $this->lnauth()->getDisplayOptions();

    $form[$key] = [
      '#type' => 'radios',
      '#title' => $this->t('Login Button Display'),
      '#options' => $options,
      '#default_value' => $default_value,
    ];

    $key = LnAuthConstants::KEY_VIEW_MODE;

    $default_value = $this->lnauth()->getViewMode();
    $options = $this->lnauth()->getViewModeOptions();

    $form[$key] = [
      '#type' => 'radios',
      '#title' => $this->t('View Mode'),
      '#default_value' => $default_value,
      '#options' => $options,
    ];

    $key = LnAuthConstants::KEY_SHOW_INSTRUCTIONS;

    $default_value = $this->lnauth()->getShowInstructions();

    $form[$key] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show instructions'),
      '#default_value' => $default_value,
    ];

    $key = LnAuthConstants::KEY_EXPIRATION;

    $default_value = $this->lnauth()->getExpiration();

    $form[$key] = [
      '#type' => 'number',
      '#title' => $this->t('Challenge Expiration'),
      '#default_value' => $default_value,
      '#min' => 60,
    ];

    $key = LnAuthConstants::KEY_FREQUENCY;

    $default_value = $this->lnauth()->getFrequency();

    $form[$key] = [
      '#type' => 'number',
      '#title' => $this->t('Login Check Frequency'),
      '#default_value' => $default_value,
      '#min' => 1000,
    ];

    $key = LnAuthConstants::KEY_ATTEMPTS;

    $default_value = $this->lnauth()->getAttempts();

    $form[$key] = [
      '#type' => 'number',
      '#title' => $this->t('Login Check Attempts'),
      '#default_value' => $default_value,
      '#min' => 0,
    ];

    $key = LnAuthConstants::KEY_PRUNE;

    $default_value = $this->lnauth()->getPrune();

    $form[$key] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Prune challenges'),
      '#default_value' => $default_value,
    ];

    $key = LnAuthConstants::KEY_PRUNE_RESPONSES;

    $default_value = $this->lnauth()->getPruneResponses();

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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();

    $this->lnauth()->saveConfiguration($values);
    $this->lnauth()->purgeCache();

    parent::submitForm($form, $form_state);
  }

}

<?php

namespace Drupal\lnauth;

/**
 * Provides the LnAuthServiceTrait trait.
 */
trait LnAuthServiceTrait {

  /**
   * Provides the lightning network authentication service.
   *
   * @var \Drupal\lnauth\LnAuthServiceInterface
   */
  protected $lnauth;

  /**
   * Provides the lightning network authentication service.
   *
   * @return \Drupal\lnauth\LnAuthServiceInterface
   */
  public function lnauth() {
    if (is_null($this->lnauth)) {
      $this->lnauth = \Drupal::service('lnauth');
    }

    return $this->lnauth;
  }

}

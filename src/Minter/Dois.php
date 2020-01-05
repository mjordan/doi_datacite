<?php

namespace Drupal\sample_minter\Minter;
namespace Drupal\doi_datacite\Minter;

/**
 * DataCite DOI minter.
 */
class Dois {

  /**
   * Returns the minter's name.
   *
   * @return string
   *   Appears in the Persistent Identifiers config form.
   */
  public function getName() {
    return t('DataCite DOI');
  }

  /**
   * Returns the minter's type.
   *
   * @return string
   *   Appears in the entity edit form next to the checkbox.
   */
  public function getPidType() {
    return t('DataCite DOI');
  }

  /**
   * Mints the identifier.
   *
   * @param object $entity
   *   The entity.
   *
   * @return string
   *   The identifier.
   */
  public function mint($entity) {
    $config = \Drupal::config('doi_datacite.settings');
    $namespace = $config->get('doi_datacite_api_endpoint');
    $namespace = $config->get('doi_datacite_prefix');
    $namespace = $config->get('doi_datacite_suffix_source');
    $namespace = $config->get('doi_datacite_username');
    $namespace = $config->get('doi_datacite_password');
    $namespace = $config->get('doi_datacite_combine_creators');
    return 'Please stand by.... the DataCite DOI module is still under development.';
  }

}

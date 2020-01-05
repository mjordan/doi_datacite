<?php

namespace Drupal\doi_datacite\Minter;

use Drupal\persistent_identifiers\MinterInterface;

/**
 * DataCite DOI minter.
 */
class Dois implements MinterInterface {

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
   *   The node, etc.
   * @param mixed $extra
   *   Extra data the minter needs, for example from the node edit form.
   *
   * @return string
   *   The identifier.
   */
  public function mint($entity, $extra = NULL) {
    $config = \Drupal::config('doi_datacite.settings');
    $namespace = $config->get('doi_datacite_api_endpoint');
    $namespace = $config->get('doi_datacite_prefix');
    $namespace = $config->get('doi_datacite_suffix_source');
    $namespace = $config->get('doi_datacite_username');
    $namespace = $config->get('doi_datacite_password');
    $namespace = $config->get('doi_datacite_combine_creators');
    // For $extra coming from node edit form.
    if (is_object($extra) && method_exists($extra, 'getValue')) {
      $datacite_resource_types = $extra->getValue('doi_datacite_resource_types_values', []);
      \Drupal::logger('doi_datacite')->debug(var_export(array_values($datacite_resource_types), true), []);
    }
    return 'Please stand by.... the DataCite DOI module is still under development.';

    // @todo: Generate DataCite XML (but see if JSON is allowed), for POSTing to DataCite API.
  }

}

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
    $api_endpoint = $config->get('doi_datacite_api_endpoint');
    $doi_prefix = $config->get('doi_datacite_prefix');
    $doi_suffix_source = $config->get('doi_datacite_suffix_source');
    $api_username = $config->get('doi_datacite_username');
    $api_password = $config->get('doi_datacite_password');
    $combine_creators = $config->get('doi_datacite_combine_creators');

    $doi = "PleseStandBy-TheDataCiteDOIModuleIsStillUnderDevelopment";

    // Generate DataCite XML for POSTing to DataCite API.
    $templated = [
      '#theme' => 'doi_datacite_metadata',
      '#entity'  => $entity,
      '#doi'  => $doi,
      '#extra' => $extra,
    ];

    $datacite_xml = \Drupal::service('renderer')->render($templated);

    // Used only during development.
    // error_log($datacite_xml . "\n", 3, '/home/vagrant/debug.log');

    // @todo: POST the XML to the DataCite API, etc.

    return $doi;
  }

}

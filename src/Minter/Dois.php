<?php

namespace Drupal\doi_datacite\Minter;

use Drupal\persistent_identifiers\MinterInterface;

/**
 * DataCite DOI minter.
 */
class Dois implements MinterInterface {

  /**
   * Constructor.
   */
  public function __construct() {
    $config = \Drupal::config('doi_datacite.settings');
    $this->api_endpoint = $config->get('doi_datacite_api_endpoint');
    $this->doi_prefix = $config->get('doi_datacite_prefix');
    $this->doi_suffix_source = $config->get('doi_datacite_suffix_source');
    $this->api_username = $config->get('doi_datacite_username');
    $this->api_password = $config->get('doi_datacite_password');
    $this->combine_creators = $config->get('doi_datacite_combine_creators');
  }

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
   *   The DOI.
   */
  public function mint($entity, $extra = NULL) {

    $doi = "PleseStandBy-TheDataCiteDOIModuleIsStillUnderDevelopment";

    // Generate DataCite XML for POSTing to DataCite API.
    $templated = [
      '#theme' => 'doi_datacite_metadata',
      '#entity'  => $entity,
      '#doi'  => $doi,
    ];

    // The renderer service uses a different render method depending on
    // the type of the $extra variable.
    if (!is_null($extra)) {
      // Check to see if $extra is from the edit form (i.e., it's
      // Drupal\Core\Form\FormState).
      if (is_object($extra) && method_exists($extra, 'getValue')) {
        $templated['#extra'] = $extra->getValue('doi_datacite_resource_type');
        $datacite_xml = \Drupal::service('renderer')->render($templated);
      }

      // Check to see if $extra is from a Drush command (i.e., it's JSON).
      // We check to see $extra is valide JSON.
      $extra_array = @json_decode($extra, TRUE);
      if (json_last_error() === JSON_ERROR_NONE) {
        $templated['#extra'] = $extra_array['resource_type'];
        error_log("From minter: " . $templated['#extra'] . "\n", 3, '/home/vagrant/debug.log');
        $datacite_xml = \Drupal::service('renderer')->renderPlain($templated);
      }
      else {
        // @todo: do something if the JSON is invalid.
      }
    }

    $success = $this->postToApi($doi, $datacite_xml);

    return $doi;
  }


  /**
   * POSTs the XML to the DataCite API.
   *
   * @param string $doi
   *   The DOI.
   * @param string $datacite_xml
   *   The DataCite XML.
   *
   * @return bool
   *   TRUE if successful, FALSE if not.
   */
  public function postToApi($doi, $datacite_xml) {
    // Used only during development.
    error_log($datacite_xml . "\n", 3, '/home/vagrant/debug.log');
    return TRUE;
  }
}

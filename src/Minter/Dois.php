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
   *
   */
  public function getResourceTypes() {
    return [
      'Audiovisual' => 'Audiovisual',
      'Collection' => 'Collection',
      'Dataset' => 'Dataset',
      'Event' => 'Event',
      'Image' => 'Image',
      'InteractiveResource' => 'InteractiveResource',
      'Model' => 'Model',
      'PhysicalObject' => 'PhysicalObject',
      'Service' => 'Service',
      'Software' => 'Software',
      'Sound' => 'Sound',
      'Text' => 'Text',
      'Workflow' => 'Workflow',
      'Other' => 'Other',
    ];
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
    // The resource's metadata must be registered via the DataCite MDS API
    // first, then its URL. See https://datacite.readme.io/docs/mds-2 for
    // additional info.
    //
    // We will create a DOI using either the node's ID or its UUID,
    // depending on the value of this module's config setting doi_datacite_suffix_source.
    // Then prepend our DOI prefix. This is inserted into the DataCite
    // metadata XML. 
    // 
    // Once we have successfully POSTed the XML to the API, we POST the URL
    // to the API. See https://github.com/SFULibrary/islandora_doi_framework/blob/7.x/modules/islandora_doi_datacite/includes/utilities.inc#L30
    // for an example.
    
    if ($this->doi_suffix_source == 'id') {
      $suffix = $entity->id();
    }
    if ($this->doi_suffix_source == 'uuid') {
      $suffix = $entity->Uuid();
    }
    $doi = $this->doi_prefix . $suffix;

    // Generate DataCite XML for POSTing to DataCite API.
    $templated = [
      '#theme' => 'doi_datacite_metadata',
      '#entity'  => $entity,
      '#doi'  => $doi,
    ];

    if (!is_null($extra)) {
      // Check to see if $extra is from the node edit form (i.e., it's
      // a Drupal\Core\Form\FormState).
      if (is_object($extra) && method_exists($extra, 'getValue')) {
        $templated['#resource_type'] = $extra->getValue('doi_datacite_resource_type');
      }

      // Check to see if $extra is from the Views Bulk Operations Action.
      if (is_array($extra)) {
        // error_log("From action via minter: " . var_export($extra, true) . "\n", 3, '/home/vagrant/debug.log');
        $templated['#resource_type'] = $extra['doi_datacite_resource_type'];
        $templated['#creator'] = $extra['doi_datacite_ceator'];
        $templated['#publication_year'] = $extra['doi_datacite_publication_year'];
        $templated['#publisher'] = $extra['doi_datacite_publisher'];
      }
      $datacite_xml = \Drupal::service('renderer')->render($templated);
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

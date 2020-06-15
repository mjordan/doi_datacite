<?php

namespace Drupal\doi_datacite\Minter;

use Drupal\persistent_identifiers\MinterInterface;
use Drupal\Core\Url;

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
   *   The node edit form state or data form Views Bulk Operations action.
   *
   * @return string
   *   The DOI that will be saved in the persister's designated field.
   */
  public function mint($entity, $extra = NULL) {
    // We will create a DOI using either the node's ID or its UUID,
    // depending on the value of this module's config setting doi_datacite_suffix_source.
    // We also provide the option to allow DataCite to autogenerate suffixes.
    // Docs are at https://support.datacite.org/docs/api-create-dois.

    global $base_url;

    if ($this->doi_suffix_source == 'id') {
      $suffix = $entity->id();
    }
    if ($this->doi_suffix_source == 'uuid') {
      $suffix = $entity->Uuid();
    }
    // @todo: We'll need to parse the auto-assigned DOI out of the request response.
    // See "Auto-generated DOI's" in https://support.datacite.org/docs/api-create-dois.
    if ($this->doi_suffix_source == 'auto') {
      $suffix = '';
    }
    $doi = $this->doi_prefix . $suffix;

      // Check to see if $extra is from the Views Bulk Operations Action (i.e.,
      // it's an array).
      // if (is_array($extra)) {
        // error_log("From action via minter: " . var_export($extra, true) . "\n", 3, '/home/vagrant/debug.log');
        // $templated['#resource_type'] = $extra['doi_datacite_resource_type'];
        // $templated['#creator'] = $extra['doi_datacite_ceator'];
        // $templated['#publication_year'] = $extra['doi_datacite_publication_year'];
        // $templated['#publisher'] = $extra['doi_datacite_publisher'];
      // }
      // $datacite_xml = \Drupal::service('renderer')->render($templated);

    // Check to see if $extra is from the node edit form (i.e., it's
    // a Drupal\Core\Form\FormState).
    if (is_object($extra) && method_exists($extra, 'getValue')) {
      $creators = explode(';', $extra->getValue('doi_datacite_creator'));
      $datacite_creators = [];
      foreach ($creators as $creator) {
        $datacite_creators[] = ['name' => $creator]; 
      }
      $datacite_array = [];
      $datacite_titles = [];
      $datacite_titles[] = ['title' => $entity->title->value];
      $datacite_array['data']['id'] = $doi;
      $datacite_array['data']['type'] = 'dois';
      $attributes = [
            'event' => 'publish',
            'doi' => $doi,
            'creators' => $datacite_creators,
            'titles' => $datacite_titles,
            'publisher' => $extra->getValue('doi_datacite_publisher'),
            'publicationYear' => $extra->getValue('doi_datacite_publication_year'),
            'types' => ['resourceTypeGeneral' => $extra->getValue('doi_datacite_resource_type')],
            'url' => $base_url . '/node/' . $entity->id(),
            'schemaVersion' => 'http://datacite.org/schema/kernel-4',
      ];
      $datacite_array['data']['attributes'] = $attributes;
      // devel_debug(json_encode($datacite_array, JSON_PRETTY_PRINT));
    }

    $datacite_json = json_encode($datacite_array);
    $success = $this->postToApi($doi, $datacite_json);

    return $doi;
  }


  /**
   * POSTs to DataCite REST API to create and publish the DOI.
   *
   * @param string $doi
   *   The DOI identifier string.
   * @param string $datacite_json
   *   The DataCite JSON.
   *
   * @return bool
   *   TRUE if successful, FALSE if not.
   */
  public function postToApi($doi, $datacite_json) {
    /*
     This is the simplest JSON we can post to create a DOI.
{
  "data": {
    "id": "10.80484/9e99eef6-07e5-4726-b59b-0008da534aa3",
    "type": "dois",
    "attributes": {
      "event": "publish",
      "doi": "10.80484/8e99eef6-07e5-4726-b59b-0008da534aa3",
      "creators": [{
        "name": "Jordan, Mark J."
      }],
      "titles": [{
        "title": "SFU Library website"
      }],
      "publisher": "SFU Library",
      "publicationYear": 2020,
      "types": {
        "resourceTypeGeneral": "Text"
      },
      "url": "https://www.lib.sfu.ca",
      "schemaVersion": "http://datacite.org/schema/kernel-4"
    }
  }
}

     */

    $response = \Drupal::httpClient()
      ->post($this->api_endpoint, [
        'auth' => [$this->api_username, $this->api_password],
        'body' => $datacite_json,
        'http_errors' => FALSE,
        'headers' => [
           'Content-Type' => 'application/vnd.api+json',
        ],
    ]);
    $response_body = $response->getBody()->getContents();

    // DataCite's API returns a 201 if the request was successful.
    devel_debug($response>getStatusCode());
    devel_debug($response_body);

    // DataCite's API returns a 404 when the user credentials or prefix are wrong, with the following body:
    // {"errors":[{"status":"404","title":"The resource you are looking for doesn't exist."}]}
    // and something like this if there is an error with the JSON:
    // {"errors":[{"status":"400","title":"You need to provide a payload following the JSONAPI spec"}]}
  }
}

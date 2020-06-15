<?php

namespace Drupal\doi_datacite\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Mints DataCite DOIs.
 *
 * @Action(
 *   id = "doi_datacite_mint_doi",
 *   label = @Translation("Mint DataCite DOIs"),
 *   type = "",
 *   confirm = TRUE,
 * )
 */
class MintDataCiteDoiAction extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface, PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $minter = \Drupal::service('doi_datacite.minter.datacitedois');
    $persister_id = \Drupal::config('persistent_identifiers.settings')->get('persistent_identifiers_persister');
    $persister = \Drupal::service($persister_id);
    // The values saved in this action's configuration form are in $this->configuration.
    $doi_identifier = $minter->mint($entity, $this->configuration);
    $persister->persist($entity, $doi_identifier);
    $this->messenger()->addMessage('"' . $entity->label() . '" assigned the DOI ' . $doi_identifier);
  }

  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Configuration form builder.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $minter = \Drupal::service('doi_datacite.minter.datacitedois');
    $resource_type_values = $minter->getResourceTypes();
    $form['doi_datacite_resource_type'] = [
      '#type' => 'radios',
      '#options' => $resource_type_values,
      '#title' => t("DataCite resource types"),
      '#required' => TRUE,
      '#description' => t("Metadata submitted to DataCite requires one of these " .
      "resource types."),
    ];
    $form['doi_datacite_creator'] = [
      '#title' => t('Creator'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t("Separate repeated values with semicolons. " .
        "This will only be used if node has no creators."),
    ];
    $form['doi_datacite_publication_year'] = [
      '#title' => t('Publication year'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t("Must be in YYYY format. Note that this value is not validated " .
        "so double check it. This will only be used if a node has no publication date."),
    ];
    $form['doi_datacite_publisher'] = [
      '#title' => t('Publisher'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t("This will only be used if a node has no publisher."),
    ];
    return $form;
  }

  /**
   * Submit handler for the action configuration form.
   *
   * @param array $form
   *   Form array.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['doi_datacite_creator'] = $form_state->getValue('doi_datacite_creator');
    $this->configuration['doi_datacite_publication_year'] = $form_state->getValue('doi_datacite_publication_year');
    $this->configuration['doi_datacite_publisher'] = $form_state->getValue('doi_datacite_publisher');
    $this->configuration['doi_datacite_resource_type'] = $form_state->getValue('doi_datacite_resource_type');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /* 
     * @todo: 'hasPermision' call is resulting in an access denied when I execute the action.
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE))
        ->andIf(\Drupal::currentUser()->hasPermission('mint persistent identifiers'));
      return $return_as_object ? $access : $access->isAllowed();
    }
    */

    return TRUE;
  }

}

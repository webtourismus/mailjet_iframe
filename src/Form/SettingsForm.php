<?php

declare(strict_types=1);

namespace Drupal\mailjet_iframe\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Mailjet Iframe settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The Mailjet v3 REST API endpoint.
   */
  protected const API_URL = 'https://api.mailjet.com/v3/REST/';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mailjet_iframe_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mailjet_iframe.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#default_value' => $this->config('mailjet_iframe.settings')->get('api_key'),
      '#required' => TRUE,
    ];
    $form['secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret key'),
      '#default_value' => $this->config('mailjet_iframe.settings')->get('secret_key'),
      '#required' => TRUE,
    ];
    $form['info'] = [
      '#type' => 'item',
      '#title' => $this->t('Notice'),
      '#description' => $this->t('Your Mailjet API Key and Secret Key can be found at <a href="https://app.mailjet.com/" target="_blank">app.mailjet.com</a>'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $response = $this->callMailjet($form_state->getValue('api_key'), $form_state->getValue('secret_key'), 'myprofile');

    if (!$response) {
      $this->messenger()->addError($this->t('Could not connect to your Mailjet account.'));
      $form_state->setErrorByName('api_key');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $apiKey = $form_state->getValue('api_key');
    $secretKey = $form_state->getValue('secret_key');

    $this->config('mailjet_iframe.settings')
      ->set('api_key', $apiKey)
      ->set('secret_key', $secretKey)
      ->save();

    // First let's check if we can reuse an existing api key
    $response = $this->callMailjet($apiKey, $secretKey, 'apitoken');
    $json = json_decode($response);
    if (!$json || !($json->Count > 0)) {
      // We did not find an existing api key, let's create a new one
      $response = $this->callMailjet(
        $apiKey,
        $secretKey,
        'apitoken',
        [
          CURLOPT_POST => TRUE,
          CURLOPT_POSTFIELDS => ['APIKeyALT' => $apiKey, 'TokenType' => 'iframe', 'IsActive' => TRUE]
        ]);
      $json = json_decode($response);
    }

    if ($json && $json->Count > 0) {
      $this->config('mailjet_iframe.settings')
        ->set('token', $json->Data[0]->Token)
        ->save();
      $this->messenger()->addMessage($this->t('Credentials successfully saved.'));
    }
    else {
      $this->messenger()->addError($this->t('Credentials were saved, but token was NOT generated! Please try again.'));
    }
  }

  /**
   * Fetches data from the Mailjet v3 REST API.
   *
   * @see https://github.com/mailjet/wordpress-mailjet-plugin-apiv3/blob/master/src/mailjetIframe/MailjetIframe.php
   */
  protected function callMailjet($apiKey, $secretKey, $apiEndpoint, $curlOptions = NULL) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, self::API_URL . $apiEndpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_USERPWD, $apiKey . ':' . $secretKey);
    if (!empty($curlOptions)) {
      curl_setopt_array($curl, $curlOptions);
    }
    // The handle is released when $curl goes out of scope; curl_close() has
    // no effect since PHP 8.0 and is deprecated since PHP 8.5.
    return curl_exec($curl);
  }
}

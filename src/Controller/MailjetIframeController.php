<?php

declare(strict_types=1);


namespace Drupal\mailjet_iframe\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Returns responses for Mailjet Iframe routes.
 */
class MailjetIframeController extends ControllerBase {

  /**
   * The base domain for all iframes.
   */
  protected const IFRAME_DOMAIN = 'https://app.mailjet.com';

  /**
   * Default Mailjet UI language
   */
  protected const DEFAULT_LANGUAGE = 'en';

  /**
   * Mapping of Drupal languages to Mailjet UI languages.
   *
   * @see https://github.com/mailjet/wordpress-mailjet-plugin-apiv3/blob/master/src/mailjetIframe/MailjetIframe.php
   */
  protected const AVAILABLE_LANGUAGES = [
    'en' => 'en_US',
    'fr' => 'fr_FR',
    'de' => 'de_DE',
    'es' => 'es_ES',
    'it' => 'it_IT',
  ];

  /**
   * Returns the access token for embedded iframes.
   */
  protected function getToken(): ?string {
    return $this->config('mailjet_iframe.settings')->get('token');
  }

  /**
   * Returns the Mailjet UI language based on the Drupal UI language.
   */
  protected function getLanguage(): string {
    $lang = $this->languageManager()->getCurrentLanguage()->getId();
    if (isset(self::AVAILABLE_LANGUAGES[$lang])) {
      return self::AVAILABLE_LANGUAGES[$lang];
    }
    return self::AVAILABLE_LANGUAGES[self::DEFAULT_LANGUAGE];
  }

  /**
   * Returns the iframe URL.
   */
  protected function getUrl(): string {
    return self::IFRAME_DOMAIN . "/campaigns?show_bar=yes&t={$this->getToken()}&locale={$this->getLanguage()}";
  }



  /**
   * Builds the iframe.
   */
  public function build(): array {
    $build = [];

    if (!$this->getToken()) {
      $build = [
        '#type' => '#markup',
        '#markup' => $this->t(
          '<p>API token not found. Please check API Key and Secret Key in the <a href="@mailjet-settings-form">Mailjet Settings</a>.<p>',
          ['@mailjet-settings-form' => Url::fromRoute('mailjet_iframe.settings')->toString()]
        ),
      ];
      return $build;
    }

    $build = [
      '#type' => 'inline_template',
      '#template' => '
        <iframe src="{{ url }}"
                allowfullscreen="true"
                style="--iframe-top: var(--drupal-displace-offset-top, var(--gin-toolbar-secondary-height, 0px)); --iframe-left: var(--drupal-displace-offset-left, 0px); border: 0 none; border-top: 2px solid black; position: fixed; z-index: 102; top: var(--iframe-top); left: var(--iframe-left); width: calc(100% - var(--iframe-left)); height: calc(100vh - var(--iframe-top));">
          {{ "Loading newsletter app..."|t }}
        </iframe>',
      '#context' => [
        'url' => $this->getUrl(),
      ],
    ];
    return $build;
  }
}

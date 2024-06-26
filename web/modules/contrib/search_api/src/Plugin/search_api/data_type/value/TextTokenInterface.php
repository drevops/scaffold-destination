<?php

namespace Drupal\search_api\Plugin\search_api\data_type\value;

/**
 * Provides an interface for text tokens.
 */
interface TextTokenInterface extends \Stringable {

  /**
   * Retrieves the text value of this token.
   *
   * @return string
   *   The text value of this token.
   */
  public function getText();

  /**
   * Sets the text value of this token.
   *
   * @param string $text
   *   The new text value of this token.
   *
   * @return $this
   */
  public function setText($text);

  /**
   * Retrieves the boost for this token.
   *
   * @return float
   *   The boost for this token.
   */
  public function getBoost();

  /**
   * Sets the boost for this token.
   *
   * @param float $boost
   *   The new boost for this token.
   *
   * @return $this
   */
  public function setBoost($boost);

}

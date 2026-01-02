<?php
/**
 * Slap Abstract Value Validator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Validate;

/**
 * @template V
 */
interface Validator {
  /**
   * Validate the Flag Values are Correct
   *
   * @param V $value
   */
  function validate($value): bool;

  /**
   * Convert Parsed Flag Value into the Correct Type
   *
   * @param V $value
   */
  function convert($value): mixed;
}
?>

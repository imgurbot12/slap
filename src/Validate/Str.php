<?php
/**
 * Slap String Validator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Flags
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Validate;

use Imgurbot12\Slap\Validate\Validator;

/**
 * @implements Validator<?string>
 */
class Str implements Validator {
  /**
   * @param ?string $value
   */
  function validate($value): bool {
    return $value === null || is_string($value);
  }

  /**
   * @param ?string $value
   */
  function convert($value): mixed {
    return strval($value);
  }
}
?>

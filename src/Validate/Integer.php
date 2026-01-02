<?php
/**
 * Slap Boolean Validator Implementation
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
class Integer implements Validator {
  /**
   * @param ?string $value
   */
  function validate($value): bool {
    return $value === null || ctype_digit($value);
  }

  /**
   * @param ?string $value
   */
  function convert($value): mixed {
    return intval($value);
  }
}
?>

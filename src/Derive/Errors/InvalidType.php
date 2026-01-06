<?php
/**
 * Slap Derive Property Invalid Type Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive\Errors;

use Imgurbot12\Slap\Derive\Errors\DeriveError;

/**
 *
 */
final class InvalidType extends DeriveError {

  function __construct(string $class, string $prop, string $type) {
    parent::__construct("$class->$prop has an invalid type annotation: $type");
  }
}
?>

<?php
/**
 * Slap Unexpected Argument Exception
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Errors;

use Imgurbot12\Slap\Errors\ParseError;

/**
 *
 */
class UnexpectedArgument extends ParseError {
  /** invalid value given */
  public mixed $value;

  /**
   * @param array<Command> $path
   */
  function __construct(array $path, mixed $value) {
    $message = "unexpected argument " . json_encode($value);
    parent::__construct($path, $message);
  }
}
?>

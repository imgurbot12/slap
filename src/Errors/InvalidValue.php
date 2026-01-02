<?php
/**
 * Slap Flag Missing Value Exception
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Errors;

use Imgurbot12\Slap\Errors\ParseError;
use Imgurbot12\Slap\Flags\Flag;
use Imgurbot12\Slap\Args\Argument;

/**
 *
 */
class InvalidValue extends ParseError {
  /** source of invalid value */
  public Argument|Flag $src;
  /** invalid value given */
  public mixed $value;

  /**
   * @param array<Command> $path
   */
  function __construct(array $path, Argument|Flag $src, mixed $value) {
    $message = "$src->name invalid value '$value'";
    parent::__construct($path, $message);
    $this->src = $src;
  }
}
?>

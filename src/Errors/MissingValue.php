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

use Imgurbot12\Slap\Args\Arg;
use Imgurbot12\Slap\Errors\ParseError;
use Imgurbot12\Slap\Flags\Flag;

/**
 *
 */
class MissingValue extends ParseError {
  /** source that is missing its value */
  public Arg|Flag $src;

  /**
   * @param array<Command> $path
   * @param Flag           $flag
   */
  function __construct(array $path, Arg|Flag $src) {
    $message = "a value is required for '$src->name'";
    parent::__construct($path, $message);
    $this->src = $src;
  }
}
?>

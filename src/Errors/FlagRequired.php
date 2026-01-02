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

use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Errors\ParseError;
use Imgurbot12\Slap\Flags\Flag;

/**
 *
 */
final class FlagRequired extends ParseError {
  /** flag that is missing its value */
  public Flag $flag;

  /**
   * @param array<Command> $path
   * @param Flag           $flag
   */
  function __construct(array $path, Flag $flag) {
    $message = "flag '--$flag->long <$flag->name>' is required but missing";
    parent::__construct($path, $message);
    $this->flag = $flag;
  }
}
?>

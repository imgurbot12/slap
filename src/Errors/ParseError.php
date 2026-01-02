<?php
/**
 * Slap Parser Error Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Errors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Errors;

use Imgurbot12\Slap\Command;

/**
 *
 */
class ParseError extends \Exception {
  /** @var array<Command> path of command/argument construction */
  public array $path;

  /**
   * @param array<Command> $path
   */
  function __construct(array $path, string $message) {
    parent::__construct($message);
    $this->path = $path;
  }
}
?>

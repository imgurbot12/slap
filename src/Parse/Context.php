<?php
/**
 * Slap Argument Parser Context Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Parser
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Parse;

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flag;

use Imgurbot12\Slap\Errors\MissingValues;

/**
 * Parser Context Tracker
 */
final class Context {
  /** @var array<Command> current path of parser */
  public array $path;
  /** @var array<Arg|Flag> missing values of current command */
  public array $missing;

  function __construct(Command ...$path) {
    $this->path    = $path;
    $this->missing = [];
  }

  function cmd(): Command {
    /** @var Command */
    return end($this->path);
  }

  /**
   * Generate New Context for a Command
   */
  function stack(Command &$command): Context {
    $path = [...$this->path, $command];
    return new Context(...$path);
  }

  function is_missing(Arg|Flag $missing): void {
    $this->missing[] = $missing;
  }

  function finalize(): void {
    if (!empty($this->missing)) throw new MissingValues($this, $this->missing);
  }
}
?>

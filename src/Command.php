<?php
/**
 * Slap Command Object Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Command
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap;

use Imgurbot12\Slap\Flags\Flag;

/**
 *
 */
class Command {
  /** primary name associated with the command */
  public string $name;
  /** usage description tied to command */
  public string $usage;
  /** @var array<Argument> arguments linked with the command */
  public array $args;
  /** @var array<Flag> flags linked with the command */
  public array $flags;
  /** @var array<Command> subcommands linked with the command */
  public array $commands;
  /** @var array<string> aliases also tied to the command */
  public array $aliases;
  /** allow for command to be repeated */
  public bool $repeat;

  /**
   * @param ?array<Argument> $args
   * @param ?array<Flag>     $flags
   * @param ?array<Command>  $commands
   * @param ?array<string>   $aliases
   */
  function __construct(
    string  $name,
    ?string $usage    = null,
    ?array  $args     = null,
    ?array  $flags    = null,
    ?array  $commands = null,
    ?array  $aliases  = null,
    bool    $repeat    = false,
  ) {
    $this->name     = $name;
    $this->usage    = $usage    ?? '';
    $this->args     = $args     ?? [];
    $this->flags    = $flags    ?? [];
    $this->commands = $commands ?? [];
    $this->aliases  = $aliases  ?? [];
    $this->repeat   = $repeat;
  }
}
?>

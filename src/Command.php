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

use Imgurbot12\Slap\Args\Arg;
use Imgurbot12\Slap\Flags\Flag;

//TODO: validate against command/flag/argument name overlap

/**
 *
 */
class Command {
  /** primary name associated with the command */
  public string $name;
  /** usage description tied to command */
  public string $about;
  /** @var array<string> command authors */
  public array $authors;
  /** command version */
  public string  $version;
  /** @var array<Argument> arguments linked with the command */
  public array $args;
  /** @var array<Flag> flags linked with the command */
  public array $flags;
  /** @var array<Command> subcommands linked with the command */
  public array $commands;
  /** @var array<string> aliases also tied to the command */
  public array $aliases;

  /**
   * @param ?array<string>   $authors
   * @param ?array<Argument> $args
   * @param ?array<Flag>     $flags
   * @param ?array<Command>  $commands
   * @param ?array<string>   $aliases
   */
  function __construct(
    string  $name,
    string  $about    = '',
    string  $version  = '0.1.0',
    ?array  $authors  = null,
    ?array  $args     = null,
    ?array  $flags    = null,
    ?array  $commands = null,
    ?array  $aliases  = null,
  ) {
    $this->name     = $name;
    $this->about    = $about;
    $this->version  = $version;
    $this->authors  = $authors  ?? [];
    $this->args     = $args     ?? [];
    $this->flags    = $flags    ?? [];
    $this->commands = $commands ?? [];
    $this->aliases  = $aliases  ?? [];
  }

  static function new(string $name): static {
    return new Command($name);
  }

  function about(string $about): self {
    $this->about = $about;
    return $this;
  }

  function version(string $version): self {
    $this->version = $version;
    return $this;
  }

  function authors(string ...$authors): self {
    array_push($this->authors, ...$authors);
    return $this;
  }

  function args(Arg ...$args): self {
    array_push($this->args, ...$args);
    return $this;
  }

  function flags(Flag ...$flags): self {
    array_push($this->flags, ...$flags);
    return $this;
  }

  function subcommands(Command ...$commands): self {
    array_push($this->commands, ...$commands);
    return $this;
  }
}
?>

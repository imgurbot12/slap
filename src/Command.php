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

use Imgurbot12\Slap\Help;
use Imgurbot12\Slap\Parse\Parser;

use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Errors\MissingValues;
use Imgurbot12\Slap\Errors\UnexpectedArg;

//TODO: validate against command/flag/argument name overlap

/**
 * Command Line Interface Builder
 */
final class Command {
  /** primary name associated with the command */
  public string $name;
  /** usage description tied to command */
  public string $about;
  /** @var array<Arg> arguments linked with the command */
  public array $args;
  /** @var array<Flag> flags linked with the command */
  public array $flags;
  /** @var array<Command> subcommands linked with the command */
  public array $commands;
  /** @var array<string> aliases also tied to the command */
  public array $aliases;

  /** @var array<string> command authors */
  public array $authors;
  /** command version */
  public string $version;

  /**
   * @param ?array<string>   $authors
   * @param ?array<Arg>      $args
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

  /**
   * Build new Command Instance
   */
  static function new(string $name): Command {
    return new Command($name);
  }

  /**
   * Configure Command About
   */
  function about(string $about): self {
    $this->about = $about;
    return $this;
  }

  /**
   * Configure Command Version
   */
  function version(string $version): self {
    $this->version = $version;
    return $this;
  }

  /**
   * Configure Command Authors
   */
  function authors(string ...$authors): self {
    array_push($this->authors, ...$authors);
    return $this;
  }

  /**
   * Configure Command Arguments
   */
  function args(Arg ...$args): self {
    array_push($this->args, ...$args);
    return $this;
  }

  /**
   * Configure Command Flags
   */
  function flags(Flag ...$flags): self {
    array_push($this->flags, ...$flags);
    return $this;
  }

  /**
   * Configure Command Subcommands
   */
  function subcommands(Command ...$commands): self {
    array_push($this->commands, ...$commands);
    return $this;
  }

  /**
   * Try to Parse the Specified Arguments or Return Null on Fail
   *
   * @param  ?array<string> $args    arguments to parse
   * @param  ?Help          $help    help page builder
   * @param  ?resource      $stderr  file resource to write error messages to
   * @return ?array<string, mixed>
   */
  function try_parse(
    ?array $args   = null,
    ?Help  $help   = null,
    mixed  $stderr = null,
  ): ?array {
    $args ??= array_slice($argv, 1);
    $help ??= new Help();
    try {
      $parser = new Parser($this);
      return $parser->parse($args);
    } catch (InvalidValue $err) {
      fwrite($stderr, $help->err_invalid($err));
    } catch (MissingValues $err) {
      fwrite($stderr, $help->err_missing($err));
    } catch (UnexpectedArg $err) {
      fwrite($stderr, $help->err_unexpected($err));
    }
    return null;
  }

  /**
   * Parse the Specified Arguments or Exit on Failure
   *
   * @param  ?array<string> $args    arguments to parse
   * @param  ?Help          $help    help page builder
   * @param  ?resource      $stderr  file resource to write error messages to
   * @return ?array<string, mixed>
   */
  function parse(
    ?array $args   = null,
    ?Help  $help   = null,
    mixed  $stderr = null,
  ): array {
    $result = $this->try_parse($args, $help, $stderr);
    if ($result === null) exit(1);
    return $result;
  }
}
?>

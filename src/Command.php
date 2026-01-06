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

//TODO: complete help pages
//TODO: builtin --help flag
//TODO: builtin help subcommand
//TODO: options to enable/disable builtin help features
//TODO: real world tests and better unit-tests
//TODO: dataclass/attribute parser implementations

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Flag;

use Imgurbot12\Slap\Help;
use Imgurbot12\Slap\Parse\Parser;

use Imgurbot12\Slap\Errors\HelpError;
use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Errors\MissingValues;
use Imgurbot12\Slap\Errors\UnexpectedArg;

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
    $this->check_duplicate_args();
    $this->check_duplicate_flags();
    $this->check_duplicate_commands();
  }

  /**
   * @return array<string>
   */
  function __names(): array {
    return [$this->name, ...$this->aliases];
  }

  /**
   * Raise Exception if Duplicate found in Args
   */
  protected function check_duplicate_args(): void {
    $reserved = [];
    foreach ($this->args as &$arg) {
      if (!in_array($arg->name, $reserved)) {
        $reserved[] = $arg->name;
        continue;
      };
      throw new \Exception("$this->name has duplicate argument '$arg->name'");
    }
  }

  /**
   * Raise Exception if Duplicate found in Flags
   */
  protected function check_duplicate_flags(): void {
    $reserved = [];
    foreach ($this->flags as &$flag) {
      if (in_array($flag->short, $reserved)) {
        throw new \Exception("$this->name has duplicate flag '-$flag->short'");
      }
      if (in_array($flag->long, $reserved)) {
        throw new \Exception("$this->name has duplicate flag '--$flag->long'");
      }
      array_push($reserved, $flag->short, $flag->long);
    }
  }

  /**
   * Raise Exception if Duplicate found in Commands
   */
  protected function check_duplicate_commands(): void {
    $reserved = [];
    foreach ($this->commands as &$cmd) {
      $aliases = [$cmd->name, ...$cmd->aliases];
      $matches = array_filter($aliases, fn ($v) => in_array($v, $reserved));
      $match   = array_shift($matches);
      if ($match !== null) {
        throw new \Exception("$this->name has duplicate subcommand '$match'");
      }
      array_push($reserved, ...$aliases);
    }
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
    $this->check_duplicate_args();
    return $this;
  }

  /**
   * Configure Command Flags
   */
  function flags(Flag ...$flags): self {
    array_push($this->flags, ...$flags);
    $this->check_duplicate_flags();
    return $this;
  }

  /**
   * Configure Command Subcommands
   */
  function subcommands(Command ...$commands): self {
    array_push($this->commands, ...$commands);
    $this->check_duplicate_commands();
    return $this;
  }

  /**
   * Try to Parse the Specified Arguments or Return ExitCode on Fail
   *
   * @param  ?array<string> $args    arguments to parse
   * @param  ?Help          $help    help page builder
   * @param  ?resource      $stderr  file resource to write error messages to
   * @return array<string, mixed>|int
   */
  function try_parse(
    ?array $args   = null,
    ?Help  $help   = null,
    mixed  $stderr = null,
  ): array|int {
    global $argv;
    if ($args === null && php_sapi_name() !== "cli") {
      throw new \Exception('PHP is not running as a CLI application');
    }
    $args   ??= array_slice($argv, 1);
    $help   ??= new Help();
    $stderr ??= STDERR;
    try {
      $parser = new Parser($help, $this);
      return $parser->parse($args);
    } catch (HelpError $err) {
      fwrite($stderr, $help->process_help($err));
      if ($err->resolved === true) return 0;
    } catch (InvalidValue $err) {
      fwrite($stderr, $help->err_invalid($err));
    } catch (MissingValues $err) {
      fwrite($stderr, $help->err_missing($err));
    } catch (UnexpectedArg $err) {
      fwrite($stderr, $help->err_unexpected($err));
    }
    return 1;
  }

  /**
   * Parse the Specified Arguments or Exit on Failure
   *
   * @param  ?array<string> $args    arguments to parse
   * @param  ?Help          $help    help page builder
   * @param  ?resource      $stderr  file resource to write error messages to
   * @return array<string, mixed>
   */
  function parse(
    ?array $args   = null,
    ?Help  $help   = null,
    mixed  $stderr = null,
  ): array {
    $result = $this->try_parse($args, $help, $stderr);
    if (is_int($result)) exit($result);
    return $result;
  }
}
?>

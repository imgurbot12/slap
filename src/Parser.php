<?php
/**
 * Slap Argument Parser Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Parser
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap;

use Imgurbot12\Slap\Command;

/**
 *
 */
class Parser {
  protected Command $command;

  function __construct(Command &$command) {
    $this->command = $command;
  }

  /**
   * Split Arguments into Sections based on Command Ownership
   *
   * @param  array<Command> $commands
   * @param  array<string>  $args
   * @param  array<Command> $path
   * @return array<string, mixed>
   */
  function split_commands(array $commands, array &$args, array $path) {
    /** @var array<integer, Command> */
    $indexes  = [];
    foreach ($args as $idx => &$arg) {
      foreach ($commands as $cidx => &$sc) {
        if ($sc->name !== $arg && !in_array($arg, $sc->aliases)) continue;
        $indexes[$idx] = $sc;
        if ($sc->repeat === false) unset($commands[$cidx]);
        break;
      }
    }

    $parsed  = [];
    $indexes = array_reverse($indexes, preserve_keys: true);
    foreach ($indexes as $idx => &$command) {
      $c_path     = [...$path, $command];
      $c_args     = array_slice(array_splice($args, $idx), 1);
      $c_commands = $this->split_commands($command->commands, $c_args, $c_path);
      $c_flags    = $this->split_flags($command->flags, $c_args, $c_path);
      //TODO: validate command arguments
      $parsed[$command->name] = [
        'commands' => $c_commands,
        'flags'    => $c_flags,
        'args'     => $c_args
      ];
    }
    return $parsed;
  }

  /**
   *
   * @param  array<Flag>    $flags
   * @param  array<string>  $args
   * @param  array<Command> $path
   * @return array<string, mixed>
   */
  function split_flags(array $flags, array &$args, array $path): array {
    /** @var array<integer, Flag> */
    $indexes = [];
    $c_flags = $flags;
    foreach ($args as $idx => &$arg) {
      foreach ($c_flags as $fidx => &$flag) {
        if ("-$flag->short" !== $arg && "--$flag->long" !== $arg) continue;
        $indexes[$idx] = $flag;
        if ($flag->repeat === false) unset($c_flags[$fidx]);
        break;
      }
    }

    /** @var array<string, array<string>> */
    $values = [];
    foreach ($indexes as $idx => &$flag) {
      array_splice($args, $idx, 1);
      $has_value = !isset($indexes[$idx + 1]) && $idx + 1 <= count($args);
      $value     = ($has_value) ? array_splice($args, $idx, 1)[0] : null;
      $values[$flag->name] ??= [];
      $values[$flag->name][] = $value;
    }

    $parsed = [];
    foreach ($flags as &$flag) {
      $f_values = $values[$flag->name] ?? null;
      if ($f_values === null) $value = '<__flag_missing>';
      elseif ($flag->repeat) $value = $f_values;
      else $value = $f_values[0];
      echo "$flag->name = " . json_encode($value) . "\n";
      $parsed[$flag->name] = $flag->finalize($path, $value);
    }
    return $parsed;
  }

  /*
   * @param string<string> $args
   * @return array<string, mixed>
   */
  function parse(array $args): array {
    $result = $this->split_commands($this->command->commands, $args, []);
    echo json_encode($result);
    return [];
  }
}
?>

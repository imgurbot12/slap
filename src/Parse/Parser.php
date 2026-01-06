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
namespace Imgurbot12\Slap\Parse;

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Help;
use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flag;

use Imgurbot12\Slap\Parse\Context;
use Imgurbot12\Slap\Errors\HelpError;
use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Errors\UnexpectedArg;

/**
 * Command Argument Parser Implementation
 */
final class Parser {
  protected Help    $help;
  protected Command $command;

  function __construct(Help &$help, Command &$command) {
    $this->help    = $help;
    $this->command = $command;
    $this->help->apply_helpers($command);
  }

  /**
   * Generate InvalidValue Valiation Error Reason from Validator ClassName
   */
  function validate_reason(Arg|Flag $obj): string {
    $path = explode('\\', $obj->validator::class);
    return strtolower(end($path));
  }

  /**
   * Validate and Finalize Argument Value for Parsing Result
   *
   * @param Arg $arg
   */
  function validate_arg(Arg &$arg, Context &$ctx, mixed $value): mixed {
    if ($value === '<__missing>') {
      if ($arg->required === true) {
        $ctx->is_missing($arg);
        return null;
      }
      return $arg->default;
    }
    if (!$arg->validator->validate($value)) {
      $cname = $this->validate_reason($arg);
      throw new InvalidValue($ctx, $arg, $value, "invalid $cname");
    }
    foreach ($arg->custom as &$v) {
      if ($v->validate($value)) continue;
      throw new InvalidValue($ctx, $arg, $value, $v->reason);
    }
    return $arg->validator->convert($value);
  }

  /**
   * Validate and Finalize Flag Value for Parsing Result
   *
   * @param Flag $flag
   */
  function validate_flag(Flag &$flag, Context &$ctx, mixed $value): mixed {
    if ($value === '<__missing>') {
      if ($flag->required === true) {
        $ctx->is_missing($flag);
        return null;
      };
      return $flag->default;
    }
    if ($value === null && $flag->requires_value) {
      $ctx->is_missing($flag);
      return null;
    }
    if (!$flag->validator->validate($value)) {
      $cname = $this->validate_reason($flag);
      throw new InvalidValue($ctx, $flag, $value, "invalid $cname");
    }
    foreach ($flag->custom as &$v) {
      if ($v->validate($value)) continue;
      throw new InvalidValue($ctx, $flag, $value, $v->reason);
    }
    return $flag->validator->convert($value);
  }

  /**
   * Search Arguments for Help Flag/Commands to Trigger Help
   *
   * @param array<integer, string> $args
   */
  function split_help(array &$args, Context &$ctx): void {
    $flags = $this->help->flag->__flags();
    foreach ($flags as $flag) {
      if (in_array($flag, $args)) throw new HelpError($ctx, []);
    }

    $commands = $this->help->command->__names();
    foreach ($args as $idx => &$arg) {
      if (!in_array($arg, $commands)) continue;
      $help_args = array_slice($args, $idx + 1);
      throw new HelpError($ctx, $help_args);
    }
  }

  /**
   * Parse Subcommands and their Parameters from the Arguments
   *
   * @param  array<Command> $commands
   * @param  array<string>  $args
   * @return array<string, mixed>
   */
  function split_commands(array $commands, array &$args, Context &$ctx) {
    /** @var array<integer, Command> */
    $indexes  = [];
    foreach ($args as $idx => &$arg) {
      foreach ($commands as $cidx => &$sc) {
        if ($sc->name !== $arg && !in_array($arg, $sc->aliases)) continue;
        $indexes[$idx] = $sc;
        unset($commands[$cidx]);
        break;
      }
    }

    $parsed  = [];
    $indexes = array_reverse($indexes, preserve_keys: true);
    foreach ($indexes as $idx => &$command) {
      $c_ctx      = $ctx->stack($command);
      $c_args     = array_slice(array_splice($args, $idx), 1);
      $c_commands = $this->split_commands($command->commands, $c_args, $c_ctx);
      $c_flags    = $this->split_flags($command->flags, $c_args, $c_ctx);
      $c_params   = $this->split_args($command->args, $c_args, $c_ctx);
      $c_ctx->finalize();
      $parsed[$command->name] = [
        'args'     => $c_params,
        'commands' => $c_commands,
        'flags'    => $c_flags,
      ];
    }
    return $parsed;
  }

  /**
   * Parse Command Flags and their Values from the Arguments
   *
   * @param  array<Flag>    $flags
   * @param  array<string>  $args
   * @return array<string, mixed>
   */
  function split_flags(array $flags, array &$args, Context &$ctx): array {
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
      /** @var integer $idx */
      array_splice($args, $idx, 1);
      $has_value = !isset($indexes[$idx + 1]) && $idx + 1 <= count($args);
      $value     = ($has_value) ? array_splice($args, $idx, 1)[0] : null;
      $values[$flag->name] ??= [];
      $values[$flag->name][] = $value;
    }

    $parsed = [];
    foreach ($flags as &$flag) {
      $f_values = $values[$flag->name] ?? null;
      if ($f_values === null) $value = '<__missing>';
      elseif ($flag->repeat) $value = $f_values;
      else $value = $f_values[0];
      $parsed[$flag->name] = $this->validate_flag($flag, $ctx, $value);
    }
    return $parsed;
  }

  /**
   * Parse Command Parameters from the Arguments
   *
   * @param  array<Arg>     $params
   * @param  array<string>  $args
   * @return array<mixed>
   */
  function split_args(array $params, array &$args, Context &$ctx): array {
    $values = [];
    foreach ($params as &$p) {
      $value = array_shift($args) ?? '<__missing>';
      $values[$p->name] = $this->validate_arg($p, $ctx, $value);
    }
    $unexpected = array_shift($args);
    if ($unexpected !== null) throw new UnexpectedArg($ctx, $unexpected);
    return $values;
  }

  /*
   * @param string<string> $args
   * @return array<string, mixed>
   */
  function parse(array $args): array {
    $ctx = new Context($this->command);
    $this->split_help($args, $ctx);

    $commands = $this->split_commands($this->command->commands, $args, $ctx);
    $flags    = $this->split_flags($this->command->flags, $args, $ctx);
    $params   = $this->split_args($this->command->args, $args, $ctx);
    $ctx->finalize();
    return ['args' => $params, 'commands' => $commands, 'flags' => $flags];
  }
}
?>

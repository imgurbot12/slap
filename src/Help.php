<?php
/**
 * Slap Help Page Generator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Help
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap;

use Imgurbot12\Slap\Colors\Ansi;
use Imgurbot12\Slap\Colors\Colors;

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flag;

use Imgurbot12\Slap\Parse\Context;
use Imgurbot12\Slap\Errors\HelpError;
use Imgurbot12\Slap\Errors\Invalid;
use Imgurbot12\Slap\Errors\Missing;
use Imgurbot12\Slap\Errors\Unexpected;

/**
 * Help Page Generator Implementation
 */
final class Help {
  /** enable coloring via ansi escape codes */
  public Colors $colors;
  /** indent to use when rendering */
  public string $indent;
  /** single space to use when rendering */
  public string $space;
  /** newline to use when rendering */
  public string $newline;

  /** standard help flag */
  public Flag $flag;
  /** standard help subcommand */
  public Command $command;

  function __construct(
    ?Colors $colors  = null,
    string  $indent  = '  ',
    string  $space   = ' ',
    string  $newline = "\n"
  ) {
    $this->colors  = $colors ?? new Ansi();
    $this->space   = $space;
    $this->indent  = $indent;
    $this->newline = $newline;
    $this->flag    = Flag::bool('help')->short('h')->about('Print help');
    $this->command = Command::new('help')
      ->about('Print this message or the help of the given subcommand(s)');
  }

  /**
   * Apply Help Flag/Subcommand to Command
   */
  function apply_helpers(Command &$command): void {
    if (!in_array($this->flag, $command->flags)) {
      $command->flags[] = $this->flag;
    }
    if (!empty($command->commands) &&
      !in_array($this->command, $command->commands)) {
      $command->commands[] = $this->command;
      foreach ($command->commands as &$cmd) {
        $this->apply_helpers($cmd);
      }
    }
  }

  /**
   * Translate Requested Help Path to Relevant Help Handler
   */
  function process_help(HelpError &$err): string {
    $command = $err->ctx->path[0];
    foreach ($err->path as $path) {
      $match = array_filter($command->commands,
        fn (Command $sc) => in_array($path, $sc->__names()));
      if (empty($match)) return $this->err_help($err->ctx, $path);
      $command  = $match[0];
      $err->ctx = $err->ctx->stack($command);
    }
    $err->exitcode ??= 0;
    return $this->help($err->ctx, $command);
  }

  /**
   * Build Space Buffered Output from Array of Items
   *
   * @template T
   * @param  array<T>           $items
   * @param  callable(T):string $left
   * @param  callable(T):string $right
   * @param  int                $threshold
   * @return string
   */
  function buffer(
    array    $items,
    callable $left,
    callable $right,
    int $threshold = 80
  ): string {
    $r1 = [];
    $r2 = [];
    foreach ($items as &$item) {
      $r1[] = ($left)($item);
      $r2[] = ($right)($item);
    }

    $buffer = 0;
    foreach ($r1 as $idx => &$line) {
      $line = $this->indent . $line;
      if (strlen($line) + strlen($r2[$idx]) > $threshold) {
        $buffer = null;
        break;
      };
      $buffer = ($buffer < strlen($line))
        ? strlen($line)
        : $buffer;
    }

    $lines = [];
    if ($buffer !== null) {
      foreach ($r1 as $idx => $a) {
        $buf = str_repeat($this->space, $buffer - strlen($a) + 1);
        $lines[] = $a . $buf . $r2[$idx];
      }
    } else {
      foreach ($r1 as $idx => $a) {
        $lines[] = $a
          . $this->newline
          . str_repeat($this->indent, 2)
          . $r2[$idx];
      }
    }
    return implode($this->newline, $lines) . $this->newline;
  }

  /**
   * Generate Message for a Command Help Page
   */
  function help(Context &$ctx, Command &$cmd): string {
    $help = $this->colors->underline('Usage:')
      . $this->colors->standard(' ')
      . $this->cmd_usage($ctx, $cmd)
      . $this->newline;
    if (!empty($cmd->args)) {
      $help .= $this->newline
        . $this->colors->underline('Arguments:')
        . $this->newline;
      $help .= $this->buffer($cmd->args,
        fn ($a) => $this->arg_usage($ctx, $a),
        fn ($a) => $this->gen_about($a),
      );
    }
    if (!empty($cmd->commands)) {
      $help .= $this->newline
        . $this->colors->underline('Commands:')
        . $this->newline;
      $help .= $this->buffer($cmd->commands,
        fn ($c) => $c->name,
        fn ($c) => $c->about,
      );
    }
    if (!empty($cmd->flags)) {
      $help .= $this->newline
        . $this->colors->underline('Options:')
        . $this->newline;
      $short = array_filter($cmd->flags, fn ($f) => $f->short !== null);
      $help .= $this->buffer($cmd->flags,
        fn ($f) => $this->flag_usage($ctx, $f, !empty($short)),
        fn ($f) => $this->gen_about($f),
      );
    }
    return $help;
  }

  /**
   * Generate Error Message for Invalid Help Request
   */
  function err_help(Context &$ctx, string $invalid): string {
    $error = $this->colors->error('error:')
      . $this->colors->standard(' unrecognized subcommand ')
      . $this->colors->warn($invalid)
      . $this->newline;
    $error .= $this->err_suffix($ctx);
    return $error;
  }

  /**
   * Generate Error Message for an Invalid Value
   */
  function err_invalid(Invalid &$err): string {
    $error = $this->colors->error('error:')
      . $this->colors->standard(' invalid value ')
      . $this->colors->warn($err->value)
      . $this->colors->standard(' for ');
    $error .= ($err->src instanceof Flag)
      ? $this->flag_usage($err->ctx, $err->src)
      : $this->arg_usage($err->ctx, $err->src);
    $error .= $this->colors->standard(': ' . $err->reason);
    $error .= str_repeat($this->newline, 2);
    $error .= $this->colors->standard("For more information, try '--help'.");
    $error .= $this->newline;
    return $error;
  }

  /**
   * Generate Error Message for Missing Values
   */
  function err_missing(Missing $err): string {
    $error = $this->colors->error('error:')
      . $this->colors->standard(' the following required arguments were not provided:')
      . $this->newline;
    foreach ($err->missing as &$missing) {
      $error .= $this->indent;
      $error .= ($missing instanceof Flag)
        ? $this->colors->highlight($this->flag_usage($err->ctx, $missing))
        : $this->colors->highlight($this->arg_usage($err->ctx, $missing));
      $error .= $this->newline;
    }
    $usage  = $this->cmd_usage($err->ctx, $err->ctx->cmd());
    $error .= $this->err_suffix($err->ctx);
    return $error;
  }

  /**
   * Generate Error Message for an Unexpected Argument
   */
  function err_unexpected(Unexpected $err): string {
    $error = $this->colors->error('error:')
      . $this->colors->standard(' unexpected argument ')
      . $this->colors->warn($err->value)
      . $this->colors->standard(' found')
      . $this->newline;
    $error .= $this->err_suffix($err->ctx);
    return $error;
  }

  /**
   * Generate Common Error Message Suffix
   */
  function err_suffix(Context &$ctx): string {
    $usage  = $this->cmd_usage($ctx, $ctx->cmd());
    $error  = $this->newline . $this->colors->underline('Usage:') . ' ';
    $error .= $this->colors->standard($usage);
    $error .= str_repeat($this->newline, 2);
    $error .= $this->colors->standard("For more information, try '--help'.");
    $error .= $this->newline;
    return $error;
  }

  /**
   * Generate About Section with Default
   */
  function gen_about(Arg|Flag $item): string {
    if ($item instanceof Flag && $item->requires_value === false) {
      return $item->about;
    }
    if ($item->default !== null) {
      $default = $item->default;
      if (is_bool($default)) $default = intval($default);
      return "$item->about [default: $default]";
    }
    return $item->about;
  }

  /**
   * Generate Argument Usage Snippet
   *
   * @param Arg $arg
   */
  function arg_usage(Context &$ctx, Arg &$arg): string {
    return ($arg->required === true)
      ? '<' . strtoupper($arg->name) . '>'
      : '[' . strtoupper($arg->name) . ']';
  }

  /**
   * Generate Flag Usage Snippet
   *
   * @param Flag $flag
   */
  function flag_usage(
    Context &$ctx, Flag &$flag, bool $use_short = false): string {
    if (!$flag->requires_value) $value = '';
    elseif ($flag->default !== null) $value = '[' . strtoupper($flag->name) . ']';
    else $value = '<' . strtoupper($flag->name) . '>';

    if ($use_short) {
      return ($flag->short !== null)
        ? "-$flag->short, --$flag->long $value"
        : str_repeat($this->space, 4) . "--$flag->long $value";
    }
    return "--$flag->long $value";
  }

  /**
   * Generate Command Usage Snippet
   */
  function cmd_usage(Context &$ctx, Command $cmd): string {
    global $argv;
    $path  = [...array_slice($ctx->path, 0, -1), $cmd];
    $usage = [];
    foreach ($path as $c) {
      $usage[] = $c->name;
      foreach ($c->flags as &$flag) {
        if (!$flag->required || $flag->default !== null) continue;
        $usage[] = $this->flag_usage($ctx, $flag);
      }
      foreach ($c->args as &$arg) {
        if ($arg->default !== null) continue;
        $usage[] = $this->arg_usage($ctx, $arg);
      }
    }
    return implode(' ', $usage);
  }
}
?>

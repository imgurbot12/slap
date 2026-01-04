<?php
/**
 * Slap Help Page Generator Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Command
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap;

use Imgurbot12\Slap\Colors\Ansi;
use Imgurbot12\Slap\Colors\Colors;

use Imgurbot12\Slap\Args\Arg;
use Imgurbot12\Slap\Command;
use Imgurbot12\Slap\Flags\Flag;

use Imgurbot12\Slap\Parse\Context;
use Imgurbot12\Slap\Errors\HelpError;
use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Errors\MissingValues;
use Imgurbot12\Slap\Errors\UnexpectedArg;

/**
 * Help Page Generator Implementation
 */
final class Help {
  /** enable coloring via ansi escape codes */
  public Colors $colors;
  /** indent to use when rendering */
  public string $indent;
  /** newline to use when rendering */
  public string $newline;

  /** standard help flag */
  public Flag $flag;
  /** standard help subcommand */
  public Command $command;

  function __construct(
    ?Colors $colors = null,
    string  $indent = '  ',
    string  $newline = "\n"
  ) {
    $this->colors  = $colors ?? new Ansi();
    $this->newline = $newline;
    $this->indent  = $indent;
    $this->flag    = Flag::bool('help')->about('Print help');
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
    }
  }

  /**
   * Translate Requested Help Path to Relevant Help Handler
   */
  function process_help(HelpError $err): string {
    $command = $err->ctx->path[0];
    foreach ($err->path as $path) {
      $match = array_filter($command->commands,
        fn (Command $sc) => $sc->name === $path || in_array($path, $sc));
      if (empty($match)) return $this->err_help($err->ctx, $path);
      $command = $match[0];
    }
    return $this->help($err->ctx, $command);
  }

  //TODO: i dont like how the help is generated for bools with no val required...
  //TODO: do arguments come before commands?
  //TODO: proper buffering / spacing determination on help-gen
  // - is 'about' buffered when on same line in clap??
  // - sometimes 'about' is indentend on line below instead of same line

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
      foreach ($cmd->args as &$arg) {
        $help .= $this->indent
          . $this->arg_usage($ctx, $arg)
          . $this->colors->standard(' ')
          . $this->colors->standard($arg->about)
          . $this->newline;
      }
    }
    if (!empty($cmd->commands)) {
      $help .= $this->newline
        . $this->colors->underline('Commands:')
        . $this->newline;
      foreach ($cmd->commands as &$cmd) {
        $names = $cmd->__names();
        $help .= $this->indent
          . $this->colors->bold(implode(', ', $names))
          . $this->newline
          . str_repeat($this->indent, 2)
          . $this->colors->standard($cmd->about)
          . $this->newline;
      }
    }
    if (!empty($cmd->flags)) {
      $help .= $this->newline
        . $this->colors->underline('Options:')
        . $this->newline;
      foreach ($cmd->flags as &$flag) {
        $help .= $this->indent
          . $this->flag_usage($ctx, $flag)
          . $this->colors->standard(' ')
          . $this->colors->standard($flag->about)
          . $this->newline;
      }
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
  function err_invalid(InvalidValue &$err): string {
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
  function err_missing(MissingValues $err): string {
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
  function err_unexpected(UnexpectedArg $err): string {
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
   * Generate Argument Usage Snippet
   *
   * @param Arg $arg
   */
  function arg_usage(Context &$ctx, Arg &$arg): string {
    return ($arg->default === null)
      ? '<' . strtoupper($arg->name) . '>'
      : '[' . strtoupper($arg->name) . ']';
  }

  /**
   * Generate Flag Usage Snippet
   *
   * @param Flag $flag
   */
  function flag_usage(Context &$ctx, Flag &$flag): string {
    $value = (!$flag->requires_value || $flag->default !== null)
      ? '[' . strtoupper($flag->name) . ']'
      : '<' . strtoupper($flag->name) . '>';
    return "--$flag->long $value";
  }

  /**
   * Generate Command Usage Snippet
   */
  function cmd_usage(Context &$ctx, Command $cmd): string {
    $usage = [];
    foreach ($cmd->flags as &$flag) $usage[] = $this->flag_usage($ctx, $flag);
    foreach ($cmd->args as &$arg) $usage[] = $this->arg_usage($ctx, $arg);
    return implode(' ', $usage);
  }
}
?>

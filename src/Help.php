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
use Imgurbot12\Slap\Errors\InvalidValue;
use Imgurbot12\Slap\Errors\MissingValues;
use Imgurbot12\Slap\Errors\ParseError;
use Imgurbot12\Slap\Errors\UnexpectedArg;

class Help {
  /** enable coloring via ansi escape codes */
  public Colors $colors;
  /** indent to use when rendering */
  public string $indent;
  /** newline to use when rendering */
  public string $newline;

  function __construct(
    ?Colors $colors = null,
    string  $indent = '  ',
    string  $newline = "\n"
  ) {
    $this->colors  = $colors ?? new Ansi();
    $this->newline = $newline;
    $this->indent  = $indent;
  }

  function err_invalid(InvalidValue &$err): string {
    return "";
  }

  /**
   * Generate Error Message for Missing Values
   */
  function err_missing(MissingValues &$err): string {
    $error = $this->colors->error("error:")
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
    $error .= $this->err_suffix($err);
    return $error;
  }

  /**
   * Generate Error Message for an Unexpected Argument
   */
  function err_unexpected(UnexpectedArg &$err): string {
    $error = $this->colors->error("error:")
      . $this->colors->standard(' unexpected argument ')
      . $this->colors->warn($err->value)
      . $this->colors->standard(' found')
      . $this->newline;
    $error .= $this->err_suffix($err);
    return $error;
  }

  /**
   * Generate Common Error Message Suffix
   */
  function err_suffix(ParseError &$err): string {
    $usage  = $this->cmd_usage($err->ctx, $err->ctx->cmd());
    $error  = $this->newline . $this->colors->underline('Usage:') . ' ';
    $error .= $this->colors->standard($usage);
    $error .= str_repeat($this->newline, 2);
    $error .= $this->colors->standard("For more information, try '--help'.");
    return $error;
  }

  /**
   * @param Arg $arg
   */
  function arg_usage(Context &$ctx, Arg &$arg): string {
    return ($arg->default === null)
      ? '<' . strtoupper($arg->name) . '>'
      : '[' . strtoupper($arg->name) . ']';
  }

  /**
   * @param Flag $flag
   */
  function flag_usage(Context &$ctx, Flag &$flag): string {
    $value = (!$flag->requires_value || $flag->default !== null)
      ? '[' . strtoupper($flag->name) . ']'
      : '<' . strtoupper($flag->name) . '>';
    return "--$flag->long $value";
  }

  function cmd_usage(Context &$ctx, Command &$cmd): string {
    $usage = [];
    foreach ($cmd->flags as &$flag) $usage[] = $this->flag_usage($ctx, $flag);
    foreach ($cmd->args as &$arg) $usage[] = $this->arg_usage($ctx, $arg);
    return implode(' ', $usage);
  }
}
?>

<?php
/**
 * Slap Help Page Colors Specification
 *
 * @package   Imgurbot12/Slap
 * @category  Colors
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Colors;

/**
 * Abstract Help Colorization Controls
 */
abstract class Colors {
  public readonly string $error;
  public readonly string $warning;
  public readonly string $highlight;
  public readonly string $underline;
  public readonly string $standard;

  /**
   * @psalm-suppress UndefinedConstant
   */
  final public function __construct() {
    /** @phpstan-ignore classConstant.notFound */
    $this->error     = static::ERROR;
    /** @phpstan-ignore classConstant.notFound */
    $this->warning   = static::WARNING;
    /** @phpstan-ignore classConstant.notFound */
    $this->highlight = static::HIGHLIGHT;
    /** @phpstan-ignore classConstant.notFound */
    $this->underline = static::UNDERLINE;
    /** @phpstan-ignore classConstant.notFound */
    $this->standard  = static::STANDARD;
  }

  final function error(string $error): string {
    return sprintf($this->error, $error);
  }

  final function warn(string $warn): string {
    return sprintf($this->warning, $warn);
  }

  final function highlight(string $highlight): string {
    return sprintf($this->highlight, $highlight);
  }

  final function underline(string $underline): string {
    return sprintf($this->underline, $underline);
  }

  final function standard(string $standard): string {
    return sprintf($this->standard, $standard);
  }
}
?>

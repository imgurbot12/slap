<?php
/**
 * Slap Command Derivation Attribute Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Derive
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive;

/**
 *
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Command {
  public ?string $name;
  public ?string $about;
  public ?string $version;
  /** @var array<string> */
  public array $authors;
  /** @var array<string> */
  public array $aliases;

  /**
   * @param array<string> $authors
   * @param array<string> $aliases
   */
  function __construct(
    ?string $name    = null,
    ?string $about   = null,
    ?string $version = null,
    ?array  $authors = null,
    ?array  $aliases = null,
  ) {
    $this->name    = $name;
    $this->about   = $about;
    $this->version = $version;
    $this->authors = $authors ?? [];
    $this->aliases = $aliases ?? [];
  }
}
?>

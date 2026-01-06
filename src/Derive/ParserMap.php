<?php
/**
 * Slap Derrivation Command Parser Object Map Implementation
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
final class ParserMap {
  /** @var class-string */
  public string $class;
  /** @var string */
  public ?string $command_prop;
  /** @var class-string */
  public ?string $command_class;
  /** @var array<string, ParserMap> **/
  public array $command_map;

  /**
   * @param class-string $class
   */
  function __construct(string $class) {
    $this->class = $class;
    $this->command_prop  = null;
    $this->command_class = null;
    $this->command_map   = [];
  }

  private function set(mixed &$class, string $key, mixed $value): void {
    if (property_exists($class, $key)) $class->{$key} = $value;
  }

  /**
   * @param array<string, array<string, mixed>> $result
   */
  function apply(array $result): mixed {
    $class = new $this->class();
    foreach ($result['args'] as $k => $v) static::set($class, $k, $v);
    foreach ($result['flags'] as $k => $v) static::set($class, $k, $v);
    if ($this->command_prop !== null && $this->command_class !== null) {
      $commands = new $this->command_class();
      foreach ($result['commands'] as $k => $v) {
        $commands->{$k} = $this->command_map[$k]->apply($v);
      }
      $class->{$this->command_prop} = $commands;
    }
    return $class;
  }
}
?>

<?php
/**
 * Slap Dataclass Command Parser Implementation
 *
 * @package   Imgurbot12/Slap
 * @category  Derive
 * @author    Andrew C Scott <imgurbot12@gmail.com>
 * @copyright MIT
 */
declare(strict_types=1);
namespace Imgurbot12\Slap\Derive;

use Imgurbot12\Slap\Derive\Flag;
use Imgurbot12\Slap\Derive\ParserMap;
use Imgurbot12\Slap\Derive\SubCommands;
use Imgurbot12\Slap\Derive\Errors\DoubleCommand;
use Imgurbot12\Slap\Derive\Errors\InvalidType;
use Imgurbot12\Slap\Derive\Errors\MissingType;

use Imgurbot12\Slap\Args\Boolean;
use Imgurbot12\Slap\Args\Integer;
use Imgurbot12\Slap\Args\Str;
use Imgurbot12\Slap\Command as CommandBuilder;
use Imgurbot12\Slap\Flags\BoolFlag;
use Imgurbot12\Slap\Flags\IntFlag;
use Imgurbot12\Slap\Flags\StrFlag;

const ARG_TYPES = [
  'string' => Str::class,
  'bool'   => Boolean::class,
  'int'    => Integer::class,
];

const FLAG_TYPES = [
  'string' => StrFlag::class,
  'bool'   => BoolFlag::class,
  'int'    => IntFlag::class,
];

/**
 * Retrieve Documentation String from Reflection Object
 */
function get_doc(\ReflectionClass|\ReflectionProperty &$ref): ?string {
  $about = $ref->getDocComment();
  if ($about === false) return null;
  return trim($about, "/* \r\n\t\0");
}

/**
 * Retrieve Required Status and Named Type Associated with Reflection Property
 *
 * @return array{bool, string}
 */
function get_type(string $class, \ReflectionProperty &$ref): array {
  $type = $ref->getType();
  if ($type === null) throw new MissingType($class, $ref->getName());
  if (!($type instanceof \ReflectionNamedType)) {
    throw new InvalidType($class, $ref->getName(), strval($type));
  }
  return [!$type->allowsNull(), $type->getName()];
}

/**
 * Render All Attribute and Instantiate their Associated Classes
 *
 * @param  array<\ReflectionAttribute> $refattrs
 * @return array<string, mixed>
 */
function render_attrs(array $refattrs): array {
  $attrs = [];
  foreach ($refattrs as $attr) $attrs[$attr->getName()] = $attr->newInstance();
  return $attrs;
}

/**
 *
 */
final class Parser {

  /**
   * @param class-string $class
   * @return array{CommandBuilder, ParserMap}
   */
  private static function build(string $class): array {
    $map     = new ParserMap($class);
    $ref     = new \ReflectionClass($class);
    $props   = $ref->getProperties();
    $command = new CommandBuilder($class);
    $command->about = get_doc($ref) ?? '';
    foreach ($props as &$prop) {
      $name     = $prop->getName();
      $default  = $prop->getDefaultValue();
      $attrs    = render_attrs($prop->getAttributes());
      [$required, $type] = get_type($class, $prop);
      $about    = get_doc($prop);

      if (is_subclass_of($type, SubCommands::class, true)) {
        if ($map->command_prop !== null) throw new DoubleCommand($class);
        $map->command_prop  = $name;
        $map->command_class = $type;
        $c_ref       = new \ReflectionClass($type);
        $subcommands = [];
        foreach ($c_ref->getProperties() as &$c_prop) {
          /** @var class-string $type */
          [,$type] = get_type($class, $c_prop);
          [$subcommand, $submap] = static::build($type);
          $subcommand->name  = $c_prop->getName();
          $subcommand->about = get_doc($c_prop) ?? $subcommand->about;
          $subcommands[] = $subcommand;
          $map->command_map[$c_prop->getName()] = $submap;
        }
        $command->subcommands(...$subcommands);
        continue;
      }

      foreach ($attrs as &$attr) {
        if ($attr instanceof Flag) {
          $fclass = FLAG_TYPES[$type] ?? null;
          if ($fclass === null) throw new InvalidType($class, $name, $type);
          $command->flags(new $fclass(
            name:     $name,
            about:    $about,
            short:    $attr->short,
            long:     $attr->long,
            required: $required,
            default:  $default,
          ));
          continue 2;
        }
      }

      $aclass = ARG_TYPES[$type] ?? null;
      if ($aclass === null) throw new InvalidType($class, $name, $type);
      $command->args(new $aclass(
        name:     $name,
        about:    $about,
        required: $required,
        default:  $default,
      ));
    }
    return [$command, $map];
  }

  /**
   * Try to Parse the Specified Arguments or Return ExitCode on Fail
   */
  static function try_parse(): static|int {
    [$command, $map] = static::build(static::class);
    $result = $command->try_parse();
    if (is_int($result)) return $result;
    return $map->apply($result);
  }

  /**
   * Parse the Specified Arguments or Exit on Failure
   */
  static function parse(): static {
    [$command, $map] = static::build(static::class);
    $result = $command->parse();
    return $map->apply($result);
  }
}

?>

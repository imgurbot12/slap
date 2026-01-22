<?php
/**
 * Slap Command UnitTests
 */
declare(strict_types=1);
namespace Itatem\Dataclass\Tests;

use PHPUnit\Framework\TestCase;

use Imgurbot12\Slap\Derive\Command;
use Imgurbot12\Slap\Derive\Flag;
use Imgurbot12\Slap\Derive\Parser;

class DeriveTest extends TestCase {

  function testSimple(): void {
    $app = new class extends Parser {
      /** foo argument */
      public string $foo;
      /** test flag */
      #[Flag(short: 't')]
      public ?int $test;
    };

    $res = $app->run(['hello']);
    $this->assertEquals('hello', $res->foo);
    $this->assertNull($res->test);

    $res = $app->run(['world', '--test', '1']);
    $this->assertEquals('world', $res->foo);
    $this->assertSame(1, $res->test);
  }
}
?>

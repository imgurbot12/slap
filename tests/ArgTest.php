<?php
/**
 * Slap Argument UnitTests
 */
declare(strict_types=1);
namespace Itatem\Dataclass\Tests;

use PHPUnit\Framework\TestCase;

use Imgurbot12\Slap\Arg;
use Imgurbot12\Slap\Command;

use Imgurbot12\Slap\Errors\Invalid;
use Imgurbot12\Slap\Errors\Missing;
use Imgurbot12\Slap\Errors\Unexpected;

class ArgTest extends TestCase {

  function testSimple(): void {
    $app = Command::new('app')
      ->args(Arg::new('test'));

    $result = $app->run(['hello']);
    $this->assertEqualsCanonicalizing(['test' => 'hello'], $result['args']);
    $this->assertEqualsCanonicalizing([], $result['commands']);
    $this->assertCount(1, $result['flags']);
    $this->assertFalse($result['flags']['help']);

    $this->expectException(Missing::class);
    $app->run([]);
  }

  function testExtraArgs(): void {
    $this->expectException(Unexpected::class);
    $this->expectExceptionMessage('unexpected argument "world"');
    Command::new('app')
      ->args(Arg::new('test'))
      ->run(['hello', 'world']);
  }

  function testDefault(): void {
    $app = Command::new('app')
      ->args(Arg::new('test')->default('hello'));

    $result = $app->run([]);
    $this->assertEquals('hello', $result['args']['test']);
    $result = $app->run(['world']);
    $this->assertEquals('world', $result['args']['test']);
  }

  function testBoolean(): void {
    $app = Command::new('app')
      ->args(Arg::bool('test'));

    $result = $app->run(['true']);
    $this->assertTrue($result['args']['test']);
    $result = $app->run(['false']);
    $this->assertFalse($result['args']['test']);
    $this->expectException(Invalid::class);
    $app->run(['yesss']);
  }

  function testInteger(): void {
    $app = Command::new('app')
      ->args(Arg::int('test'));

    $result = $app->run(['1234']);
    $this->assertSame(1234, $result['args']['test']);
    $this->expectException(Invalid::class);
    $app->run(['123hello']);
  }

  function testCustomValidator(): void {
    $app = Command::new('app')
      ->args(Arg::new('test')
        ->validate(fn ($v) => $v === 'hello', 'invalid hello'));

    $result = $app->run(['hello']);
    $this->assertEquals('hello', $result['args']['test']);
    $this->expectException(Invalid::class);
    $this->expectExceptionMessage('test = "world" invalid hello');
    $app->run(['world']);
  }
}
?>

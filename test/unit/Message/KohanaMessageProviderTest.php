<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\Message;


use Ingenerator\KohanaExtras\Message\KohanaMessageProvider;
use Kohana;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;
use function var_export;

class KohanaMessageProviderTest extends TestCase
{
    private ?array $old_modules;

    private ?string $tmp_module_path = NULL;

    private TestLogger $log;

    public function test_it_is_initialisable(): void
    {
        $this->assertInstanceOf(KohanaMessageProvider::class, $this->newSubject());
    }

    public function test_it_provides_message_from_message_file(): void
    {
        $file = uniqid('test');

        $this->givenMessageFile(
            $file,
            ['some' => ['message' => 'some message']]
        );
        $this->assertSame(
            'some message',
            $this->newSubject()->message($file, 'some.message')
        );
    }

    public function test_it_interpolates_provided_parameters(): void
    {
        $file = uniqid('test');

        $this->givenMessageFile(
            $file,
            ['message' => 'it has :param and :other']
        );
        $this->assertSame(
            'it has this and that',
            $this->newSubject()->message($file, 'message', [':param' => 'this', ':other' => 'that'])
        );
    }

    public function test_it_returns_default_if_message_does_not_exist(): void
    {
        $file = uniqid();
        $this->assertSame(
            'some thing here',
            $this->newSubject()->message(
                $file,
                'message.path',
                [':message' => 'thing'],
                'some :message here'
            )
        );
    }

    public function test_it_returns_message_path_if_message_file_does_not_exist_and_no_default(): void
    {
        $file = uniqid();
        $this->assertSame(
            $file.':message.path',
            $this->newSubject()->message($file, 'message.path')
        );
    }

    public function test_it_returns_message_path_if_message_is_undefined_and_no_default(): void
    {
        $file = uniqid();
        $this->givenMessageFile(
            $file,
            []
        );
        $this->assertSame(
            $file.':message.path',
            $this->newSubject()->message($file, 'message.path')
        );
    }

    public function test_it_logs_nothing_if_message_is_defined(): void
    {
        $file = uniqid();
        $this->givenMessageFile($file, ['thing' => 'other']);
        $this->newSubject()->message($file, 'thing');
        $this->assertEmpty($this->log->records, 'Should log nothing');
    }

    public function test_it_logs_nothing_if_message_is_defaulted(): void
    {
        $file = uniqid();
        $this->givenMessageFile($file, []);
        $this->newSubject()->message($file, 'thing', [], 'some default text');
        $this->assertEmpty($this->log->records, 'Should log nothing');
    }

    public function test_it_logs_warning_if_message_is_undefined(): void
    {
        $file = uniqid();
        $this->givenMessageFile($file, []);
        $this->newSubject()->message($file, 'thing.stuff');
        $this->assertTrue($this->log->hasWarningThatContains("Undefined message '$file:thing.stuff'"));
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->log = new TestLogger;
    }

    public function tearDown(): void
    {
        if ($this->tmp_module_path) {
            shell_exec('rm -rf '.$this->tmp_module_path);
            $this->assertFalse(is_dir($this->tmp_module_path), 'Temp path should have been removed');
        }
        if (isset($this->old_modules)) {
            Kohana::modules($this->old_modules);
        }
        parent::tearDown();
    }

    private function newSubject(): KohanaMessageProvider
    {
        return new KohanaMessageProvider($this->log);
    }

    private function givenMessageFile($file, $messages): void
    {
        $this->createTempModule();
        file_put_contents(
            "{$this->tmp_module_path}/messages/$file.php",
            '<?php return '.var_export($messages, TRUE).';'
        );
    }

    private function createTempModule(): void
    {
        if ($this->tmp_module_path) {
            return;
        }

        $module_name           = uniqid('kohanawrapper');
        $this->tmp_module_path = sys_get_temp_dir().'/'.$module_name;
        $this->assertTrue(mkdir($this->tmp_module_path), 'Created temporary directory');
        $this->assertTrue(mkdir($this->tmp_module_path.'/messages'), 'Created temporary messages directory');

        $modules               = $this->old_modules = Kohana::modules();
        $modules[$module_name] = $this->tmp_module_path;
        Kohana::modules($modules);
    }

}

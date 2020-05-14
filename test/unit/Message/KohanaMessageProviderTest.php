<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   proprietary
 */

namespace test\unit\Ingenerator\KohanaExtras\Message;


use Ingenerator\KohanaExtras\Message\KohanaMessageProvider;
use Psr\Log\Test\TestLogger;

class KohanaMessageProviderTest extends \PHPUnit\Framework\TestCase
{
    protected $old_modules;

    protected $tmp_module_path;

    /**
     * @var TestLogger
     */
    protected $log;

    public function test_it_is_initialisable()
    {
        $this->assertInstanceOf(
            KohanaMessageProvider::class,
            $this->newSubject()
        );
    }

    public function test_it_provides_message_from_message_file()
    {
        $file = \uniqid('test');

        $this->givenMessageFile(
            $file,
            ['some' => ['message' => 'some message']]
        );
        $this->assertSame(
            'some message',
            $this->newSubject()->message($file, 'some.message')
        );
    }

    public function test_it_interpolates_provided_parameters()
    {
        $file = \uniqid('test');

        $this->givenMessageFile(
            $file,
            ['message' => 'it has :param and :other']
        );
        $this->assertSame(
            'it has this and that',
            $this->newSubject()->message($file, 'message', [':param' => 'this', ':other' => 'that'])
        );
    }

    public function test_it_returns_default_if_message_does_not_exist()
    {
        $file = \uniqid();
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

    public function test_it_returns_message_path_if_message_file_does_not_exist_and_no_default()
    {
        $file = \uniqid();
        $this->assertSame(
            $file.':message.path',
            $this->newSubject()->message($file, 'message.path')
        );
    }

    public function test_it_returns_message_path_if_message_is_undefined_and_no_default()
    {
        $file = \uniqid();
        $this->givenMessageFile(
            $file,
            []
        );
        $this->assertSame(
            $file.':message.path',
            $this->newSubject()->message($file, 'message.path')
        );
    }

    public function test_it_logs_nothing_if_message_is_defined()
    {
        $file = \uniqid();
        $this->givenMessageFile($file, ['thing' => 'other']);
        $this->newSubject()->message($file, 'thing');
        $this->assertEmpty($this->log->records, 'Should log nothing');
    }

    public function test_it_logs_nothing_if_message_is_defaulted()
    {
        $file = \uniqid();
        $this->givenMessageFile($file, []);
        $this->newSubject()->message($file, 'thing', [], 'some default text');
        $this->assertEmpty($this->log->records, 'Should log nothing');
    }

    public function test_it_logs_warning_if_message_is_undefined()
    {
        $file = \uniqid();
        $this->givenMessageFile($file, []);
        $this->newSubject()->message($file, 'thing.stuff');
        $this->assertTrue($this->log->hasWarningThatContains("Undefined message '$file:thing.stuff'"));
    }

    public function setUp()
    {
        parent::setUp();
        $this->log = new TestLogger;
    }

    public function tearDown()
    {
        if ($this->tmp_module_path) {
            `rm -rf $this->tmp_module_path`;
            $this->assertFalse(\is_dir($this->tmp_module_path), 'Temp path should have been removed');
        }
        if ($this->old_modules) {
            \Kohana::modules($this->old_modules);
        }
        parent::tearDown();
    }

    protected function newSubject()
    {
        return new KohanaMessageProvider($this->log);
    }

    protected function givenMessageFile($file, $messages)
    {
        $this->createTempModule();
        \file_put_contents(
            "{$this->tmp_module_path}/messages/$file.php",
            '<?php return '.\var_export($messages, TRUE).';'
        );
    }

    protected function createTempModule()
    {
        if ($this->tmp_module_path) {
            return;
        }

        $this->tmp_module_path = \sys_get_temp_dir().'/'.\uniqid('kohanawrapper');
        $this->assertTrue(\mkdir($this->tmp_module_path), 'Created temporary directory');
        $this->assertTrue(\mkdir($this->tmp_module_path.'/messages'), 'Created temporary messages directory');

        $modules   = $this->old_modules = \Kohana::modules();
        $modules[] = $this->tmp_module_path;
        \Kohana::modules($modules);
    }

}

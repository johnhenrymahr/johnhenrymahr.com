<?php
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    protected $configMock;

    protected $obj;

    protected $logfile;

    protected function setUp()
    {
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $root = \org\bovigo\vfs\vfsStream::setup('server');
        $datadir = \org\bovigo\vfs\vfsStream::newDirectory('data')->at($root);
        $logdir = \org\bovigo\vfs\vfsStream::newDirectory('logs')->at($datadir);
        $this->logfile = \org\bovigo\vfs\vfsStream::newFile('jhm-system.log')->at($logdir);
        $this->configMock->shouldReceive('getStorage')->with('logs')->once()->andReturn($logdir->url() . '/');
        $this->obj = new \JHM\Logger($this->configMock);
    }

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testFailedSetup()
    {
        $this->expectException(\JHM\JhmException::class);
        $this->configMock->shouldReceive('getStorage')->with('logs')->once()->andReturn('/path/to/nothinbg');
        $obj = new \JHM\Logger($this->configMock);
    }

    public function testLogWrite()
    {
        $message = 'A message of the test sort';
        $this->obj->log('DEBUG', $message);
        $content = $this->logfile->getContent();
        $this->assertTrue((boolean) strpos($content, $message));
        $this->assertTrue((boolean) strpos($content, 'DEBUG'));
    }

    public function testIgnoreInvalid()
    {
        $expected = 'Bad Log Format';
        $this->obj->log('WHATEVER', 'some stuff');
        $content = $this->logfile->getContent();
        $this->assertTrue((boolean) strpos($content, $expected));
    }

}

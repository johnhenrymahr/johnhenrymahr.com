<?php
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $fileLoaderMock;

    protected $loggerMock;

    protected $csrfMock;

    protected function setUp()
    {
        $this->csrfMock = \Mockery::mock('\JHM\CsrfTokenInterface');
        $this->csrfMock->shouldReceive('getField')->andReturn('tokenField');
        $this->csrfMock->shouldReceive('generateToken')->with('contact')->andReturn('contactToken');
        $this->csrfMock->shouldReceive('generateToken')->with('cv')->andReturn('cvToken');
        $this->fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->obj = new \JHM\DataProvider($this->fileLoaderMock, $this->loggerMock, $this->csrfMock);
    }
    protected function tearDown()
    {
        \Mockery::close();
    }
    public function testGetTemplateModel()
    {
        $this->assertTrue(is_array($this->obj->getTemplateModel('templateid')));
    }
    public function testGetBootstrapData()
    {
        $this->csrfMock->shouldReceive('generateToken')->andReturn('test-token');
        $expectation = array(
            '_moduleData' => array(
                'contact' => array(
                    'tokenField' => 'contactToken',
                ),
                'cv' => array(
                    'tokenField' => 'cvToken',
                ),
            ),
        );
        $this->assertTrue(is_array($this->obj->getBootstrapData()));
        $this->assertEquals($expectation, $this->obj->getBootstrapData());
    }
}

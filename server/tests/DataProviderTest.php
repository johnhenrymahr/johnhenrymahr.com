<?php
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    protected $obj;

    protected $fileLoaderMock;

    protected $loggerMock;

    protected function setUp()
    {
        $this->fileLoaderMock = \Mockery::mock('\JHM\FileLoaderInterface');
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->obj = new \JHM\DataProvider($this->fileLoaderMock, $this->loggerMock);
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
        $this->assertTrue(is_array($this->obj->getBootstrapData()));
    }
}

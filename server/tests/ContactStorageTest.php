<?php

class ContactStorageTest extends \PHPUnit\Framework\TestCase
{

    protected $dbFactoryMock;

    protected $loggerMock;

    protected $configMock;

    protected $db;

    protected $obj;

    protected $root;

    protected function setUp()
    {

        $this->db = \Mockery::mock();
        $this->db->shouldReceive('connect');
        $this->db->shouldReceive('ping')->once()->andReturn(true);
        $this->dbFactoryMock = \Mockery::mock('\JHM\dbFactoryInterface');
        $this->dbFactoryMock->shouldReceive('getDB')->andReturn($this->db);
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->configMock = \Mockery::mock('\JHM\ConfigInterface');
        $this->root = \org\bovigo\vfs\vfsStream::setup('downloads');
        $this->obj = new \JHM\ContactStorage($this->dbFactoryMock, $this->configMock, $this->loggerMock);
    }

    public function testContactStorage()
    {
        $data = array(
            'email' => 'joe@mail.com',
            'name' => 'joe dude',
            'phone' => '232-434-2323',
        );
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('getOne')->once()->andReturn([]);
        $this->db->shouldReceive('insert')->once()->with('contact', $data)->andReturn('34');
        $this->assertEquals('34', $this->obj->addContact('joe@mail.com', 'joe dude ', null, '232-434-2323'));
    }

    public function testContactStorageUpdate()
    {
        $data = array(
            'email' => 'joe@mail.com',
            'name' => 'joe dude',
            'phone' => '232-434-2323',
        );
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('getOne')->once()->andReturn(['id' => '54']);
        $this->db->shouldReceive('update')->once()->with('contact', $data)->andReturn(true);
        $this->db->shouldReceive('insert');
        $this->db->count = 1;
        $this->assertEquals('54', $this->obj->addContact('joe@mail.com', 'joe dude ', null, '232-434-2323'));
    }

    public function testAddDownloadRecord()
    {
        \org\bovigo\vfs\vfsStream::newFile('testfile')->at($this->root);
        $this->configMock->shouldReceive('getStorage')->with('downloads')->once()->andReturn($this->root->url() . '/');
        $this->db->shouldReceive('where')->with('cid', '22');
        $this->db->shouldReceive('where')->with('active', '1');
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('get')->with('download');
        $this->db->count = 0;
        $this->db->shouldReceive('getOne')->with('download')->andReturn([]);
        $this->db->shouldReceive('insert')->once()->andReturn('23');
        $a = $this->obj->addDownloadRecord('22', 'joe@mail.com', 'testfile');
        $this->assertTrue(is_string($a));
        $this->assertEquals(strlen($a), 40);
    }

    public function testAddDownloadRecordFailure()
    {
        \org\bovigo\vfs\vfsStream::newFile('testfile2')->at($this->root);
        $this->configMock->shouldReceive('getStorage')->with('downloads')->once()->andReturn($this->root->url() . '/');
        $this->db->shouldReceive('where')->with('cid', '22');
        $this->db->shouldReceive('where')->with('active', '1');
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('get')->with('download');
        $this->db->count = 0;
        $this->db->shouldReceive('getOne')->with('download')->andReturn([]);
        $this->db->shouldReceive('insert')->once()->andReturn(false);
        $this->db->shouldReceive('getLastError')->andReturn('error string');
        $this->db->shouldReceive('getLastQuery')->andReturn('sql query');
        $this->loggerMock->shouldReceive('log')->once();
        $a = $this->obj->addDownloadRecord('22', 'joe@mail.com', 'testfile2');
        $this->assertFalse($a);
    }

    public function testRemoveDownloadToken()
    {
        $this->db->shouldReceive('where')->with('token', '2223');
        $this->db->shouldReceive('delete')->with('download')->andReturn(true);
        $this->assertTrue($this->obj->removeDownloadToken('2223'));
    }

    public function testValidateDownloadToken()
    {
        $record = [
            'id' => '23',
            'token' => '231d3',
            'access' => 2,
            'fileId' => 'testfile',
            'fileMimeType' => 'application/domain',
        ];
        $this->configMock->shouldReceive('get')->with('downloads.cvMax')->andReturn(7);
        $this->db->shouldReceive('get')->with('download')->andReturn([$record]);
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('update')->withArgs(array('download', array('access' => 3)))->andReturn(true);
        $this->db->shouldReceive('update')->andReturn(true);
        $this->db->count = 1;
        $record['access'] = 3;
        $this->assertEquals($record, $this->obj->validateDownloadToken('231d3'));
    }

    public function testValidateDownloadTokenExpired()
    {
        $record = [
            'id' => '23',
            'token' => '231d3',
            'access' => 9,
            'fileId' => 'testfile',
        ];
        $this->configMock->shouldReceive('get')->with('downloads.cvMax')->andReturn(7);
        $this->db->shouldReceive('get')->with('download')->andReturn([$record]);
        $this->db->shouldReceive('getLastError')->andReturn('error string');
        $this->db->shouldReceive('getLastQuery')->andReturn('sql query');
        $this->loggerMock->shouldReceive('log')->once();
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('update')->withArgs(array('download', array('active' => '0')));
        $this->db->shouldReceive('update');
        $this->assertFalse($this->obj->validateDownloadToken('231d3'));
    }

    public function testValidateDownloadTokenEmptySet()
    {
        $this->db->shouldReceive('get')->with('download')->andReturn([]);
        $this->db->shouldReceive('where');
        $this->db->shouldReceive('update');
        $this->assertFalse($this->obj->validateDownloadToken('231d3'));
    }

    public function testContactStorageBadMail()
    {
        $this->assertEquals(false, $this->obj->addContact('joe@mail', 'joe dude ', '232-434-2323'));
    }

    public function testMessageStorage()
    {
        $data = array(
            'cid' => '32',
            'topic' => 'test',
            'message' => 'foo bar bar',
        );
        $this->db->shouldReceive('insert')->once()->with('message', $data)->andReturn('34');
        $this->assertEquals('34', $this->obj->addMessage('32', 'test', 'foo bar bar'));
    }
}

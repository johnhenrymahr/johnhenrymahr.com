<?php

class ContactStorageTest extends \PHPUnit\Framework\TestCase
{

    protected $dbFactoryMock;

    protected $loggerMock;

    protected $dbMock;

    protected $db;

    protected $obj;

    protected function setUp()
    {

        $this->db = \Mockery::mock();
        $this->db->shouldReceive('connect');
        $this->dbFactoryMock = \Mockery::mock('\JHM\dbFactoryInterface');
        $this->dbFactoryMock->shouldReceive('getDB')->andReturn($this->db);
        $this->loggerMock = \Mockery::mock('\JHM\LoggerInterface');
        $this->obj = new \JHM\ContactStorage($this->dbFactoryMock, $this->loggerMock);
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
        $this->assertEquals('34', $this->obj->addContact('joe@mail.com', 'joe dude ', '232-434-2323'));
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
        $this->db->shouldReceive('update')->once()->with('contact', $data);
        $this->assertEquals('54', $this->obj->addContact('joe@mail.com', 'joe dude ', '232-434-2323'));
    }

    public function testContactStorageBadMail()
    {
        $data = array(
            'email' => 'joe@mail.com',
            'name' => 'joe dude',
            'phone' => '232-434-2323',
        );
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

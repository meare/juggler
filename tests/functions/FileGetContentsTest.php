<?php


use org\bovigo\vfs\vfsStream;

class FileGetContentsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('home');
    }

    public function testFileDoesNotExist()
    {
        $this->setExpectedException(\RuntimeException::class);

        \Meare\Juggler\file_get_contents(vfsStream::url('home/foo/bar'));
    }

    public function testFileExists()
    {
        file_put_contents(vfsStream::url('home/file.txt'), 'foo bar');

        $this->assertSame(
            'foo bar',
            \Meare\Juggler\file_get_contents(vfsStream::url('home/file.txt'))
        );
    }
}

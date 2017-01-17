<?php


use org\bovigo\vfs\vfsStream;

class FilePutContentsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        vfsStream::setup('home');
    }

    public function testDirectoryExists()
    {
        \Meare\Juggler\file_put_contents(vfsStream::url('home/file.txt'), 'foo bar');

        $this->assertSame(
            'foo bar',
            file_get_contents(vfsStream::url('home/file.txt'))
        );
    }

    public function testDirectoryDoesNotExist()
    {
        $this->setExpectedException(\RuntimeException::class);

        \Meare\Juggler\file_put_contents(vfsStream::url('home/foo/bar.txt'), 'foo bar');
    }
}

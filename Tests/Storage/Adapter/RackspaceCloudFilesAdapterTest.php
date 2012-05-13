<?php

namespace Vich\UploaderBundle\Tests\Storage\Adapter;

use Vich\UploaderBundle\Storage\Adapter\RackspaceCloudFilesAdapter;

/**
 * Description of RackspaceCloudFilesAdapter
 *
 * @author ftassi
 */
class RackspaceCloudFilesAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testPut()
    {
        $CDNAuth = $this->getMockBuilder('\CF_Authentication')
                ->setMethods(array('authenticate'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNConnection = $this->getMockBuilder('\CF_Connection')
                ->setMethods(array('get_container'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNContainer = $this->getMockBuilder('\CF_Container')
                ->setMethods(array('create_object'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNObject = $this->getMockBuilder('\CF_Object')
                ->setMethods(array('load_from_filename'))
                ->disableOriginalConstructor()
                ->getMock();
        
        
        $CDNObject->expects($this->once())
                ->method('load_from_filename')
                ->with('/tmp/file.jpg')
                ->will($this->returnValue(true));
        
        $CDNContainer->expects($this->once())
                ->method('create_object')
                ->with('file.jpg')
                ->will($this->returnValue($CDNObject));
        
        $CDNAuth->expects($this->once())
                ->method('authenticate');
        
        $CDNConnection->expects($this->once())
                ->method('get_container')
                ->with('remote_media_container')
                ->will($this->returnValue($CDNContainer));
        
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->at(0))
                ->method('get')
                ->with('rackspacecloudfiles_authentication')
                ->will($this->returnValue($CDNAuth));
        $container->expects($this->at(1))
                ->method('get')
                ->with('rackspacecloudfiles_connection')
                ->will($this->returnValue($CDNConnection));
        
        $adapter = new RackspaceCloudFilesAdapter();
        $adapter->setContainer($container);
        $adapter->setRackspaceConnection($CDNConnection);
        $adapter->setMediaContainer('remote_media_container');
        $response  = $adapter->put('/tmp/file.jpg', 'file.jpg');
        
        $this->assertTrue($response);
    }
    
    public function testGetAbsoluteUri()
    {
        $CDNAuth = $this->getMockBuilder('\CF_Authentication')
                ->setMethods(array('authenticate'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNConnection = $this->getMockBuilder('\CF_Connection')
                ->setMethods(array('get_container'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNContainer = $this->getMockBuilder('\CF_Container')
                ->setMethods(array('get_object'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNObject = $this->getMockBuilder('\CF_Object')
                ->setMethods(array('public_uri'))
                ->disableOriginalConstructor()
                ->getMock();
        
        
        $CDNAuth->expects($this->once())
                ->method('authenticate');
        
        $CDNConnection->expects($this->once())
                ->method('get_container')
                ->with('remote_media_container')
                ->will($this->returnValue($CDNContainer));
        
        $CDNContainer->expects($this->once())
                ->method('get_object')
                ->with('file.jpg')
                ->will($this->returnValue($CDNObject));
        
        $CDNObject->expects($this->once())
                ->method('public_uri')
                ->will($this->returnValue('http://cdn.com/file.jpg'));
        
        
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->at(0))
                ->method('get')
                ->with('rackspacecloudfiles_authentication')
                ->will($this->returnValue($CDNAuth));
        $container->expects($this->at(1))
                ->method('get')
                ->with('rackspacecloudfiles_connection')
                ->will($this->returnValue($CDNConnection));
        
        $adapter = new RackspaceCloudFilesAdapter();
        $adapter->setContainer($container);
        $adapter->setRackspaceConnection($CDNConnection);
        $adapter->setMediaContainer('remote_media_container');
        $response  = $adapter->getAbsoluteUri('file.jpg');
        
        $this->assertEquals('http://cdn.com/file.jpg', $response);
    }
    
    public function testRemove()
    {
        
        $CDNAuth = $this->getMockBuilder('\CF_Authentication')
                ->setMethods(array('authenticate'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNConnection = $this->getMockBuilder('\CF_Connection')
                ->setMethods(array('get_container'))
                ->disableOriginalConstructor()
                ->getMock();
        
        $CDNContainer = $this->getMockBuilder('\CF_Container')
                ->setMethods(array('delete_object'))
                ->disableOriginalConstructor()
                ->getMock();
        
        
        $CDNAuth->expects($this->once())
                ->method('authenticate');
        
        $CDNConnection->expects($this->once())
                ->method('get_container')
                ->with('remote_media_container')
                ->will($this->returnValue($CDNContainer));
        
        $CDNContainer->expects($this->once())
                ->method('delete_object')
                ->with('file.jpg')
                ->will($this->returnValue(true));
        
        
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->at(0))
                ->method('get')
                ->with('rackspacecloudfiles_authentication')
                ->will($this->returnValue($CDNAuth));
        $container->expects($this->at(1))
                ->method('get')
                ->with('rackspacecloudfiles_connection')
                ->will($this->returnValue($CDNConnection));
        
        $adapter = new RackspaceCloudFilesAdapter();
        $adapter->setContainer($container);
        $adapter->setRackspaceConnection($CDNConnection);
        $adapter->setMediaContainer('remote_media_container');
        $response  = $adapter->remove('file.jpg');
        
        $this->assertTrue($response);
    }
}
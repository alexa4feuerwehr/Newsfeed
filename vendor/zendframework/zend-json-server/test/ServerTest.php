<?php
/**
 * @see       https://github.com/zendframework/zend-json-server for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-json-server/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Json\Server;

use PHPUnit\Framework\TestCase;
use Zend\Json;
use Zend\Json\Server;
use Zend\Json\Server\Request;
use Zend\Json\Server\Response;
use Zend\Json\Server\Error;
use Zend\Server\Reflection\Exception\RuntimeException;

class ServerTest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->server = new Server\Server();
    }

    public function testShouldBeAbleToBindFunctionToServer()
    {
        $this->server->addFunction('strtolower');
        $methods = $this->server->getFunctions();
        $this->assertTrue($methods->hasMethod('strtolower'));
    }

    public function testShouldBeAbleToBindCallbackToServer()
    {
        try {
            $this->server->addFunction([$this, 'setUp']);
        } catch (RuntimeException $e) {
            $this->markTestIncomplete('PHPUnit docblocks may be incorrect');
        }
        $methods = $this->server->getFunctions();
        $this->assertTrue($methods->hasMethod('setUp'));
    }

    public function testShouldBeAbleToBindClassToServer()
    {
        $this->server->setClass(Server\Server::class);
        $test = $this->server->getFunctions();
        $this->assertNotEmpty($test);
    }

    public function testBindingClassToServerShouldRegisterAllPublicMethods()
    {
        $this->server->setClass(Server\Server::class);
        $test = $this->server->getFunctions();
        $methods = get_class_methods(Server\Server::class);
        foreach ($methods as $method) {
            if ('_' == $method[0]) {
                continue;
            }
            $this->assertTrue(
                $test->hasMethod($method),
                'Testing for method ' . $method . ' against ' . var_export($test, 1)
            );
        }
    }

    public function testShouldBeAbleToBindObjectToServer()
    {
        $object = new Server\Server();
        $this->server->setClass($object);
        $test = $this->server->getFunctions();
        $this->assertNotEmpty($test);
    }

    public function testBindingObjectToServerShouldRegisterAllPublicMethods()
    {
        $object = new Server\Server();
        $this->server->setClass($object);
        $test = $this->server->getFunctions();
        $methods = get_class_methods($object);
        foreach ($methods as $method) {
            if ('_' == $method[0]) {
                continue;
            }
            $this->assertTrue(
                $test->hasMethod($method),
                'Testing for method ' . $method . ' against ' . var_export($test, 1)
            );
        }
    }

    public function testShouldBeAbleToBindMultipleClassesAndObjectsToServer()
    {
        $this->server->setClass(Server\Server::class)
                     ->setClass(new Json\Json());
        $methods = $this->server->getFunctions();
        $zjsMethods = get_class_methods(Server\Server::class);
        $zjMethods  = get_class_methods(Json\Json::class);
        $this->assertGreaterThan(count($zjsMethods), count($methods));
        $this->assertGreaterThan(count($zjMethods), count($methods));
    }

    public function testNamingCollisionsShouldResolveToLastRegisteredMethod()
    {
        $this->server->setClass(Request::class)
                     ->setClass(Response::class);
        $methods = $this->server->getFunctions();
        $this->assertTrue($methods->hasMethod('toJson'));
        $toJSON = $methods->getMethod('toJson');
        $this->assertEquals(Response::class, $toJSON->getCallback()->getClass());
    }

    public function testGetRequestShouldInstantiateRequestObjectByDefault()
    {
        $request = $this->server->getRequest();
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testShouldAllowSettingRequestObjectManually()
    {
        $orig = $this->server->getRequest();
        $new  = new Request();
        $this->server->setRequest($new);
        $test = $this->server->getRequest();
        $this->assertSame($new, $test);
        $this->assertNotSame($orig, $test);
    }

    public function testGetResponseShouldInstantiateResponseObjectByDefault()
    {
        $response = $this->server->getResponse();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testShouldAllowSettingResponseObjectManually()
    {
        $orig = $this->server->getResponse();
        $new  = new Response();
        $this->server->setResponse($new);
        $test = $this->server->getResponse();
        $this->assertSame($new, $test);
        $this->assertNotSame($orig, $test);
    }

    public function testFaultShouldCreateErrorResponse()
    {
        $response = $this->server->getResponse();
        $this->assertFalse($response->isError());
        $this->server->fault('error condition', -32000);
        $this->assertTrue($response->isError());
        $error = $response->getError();
        $this->assertEquals(-32000, $error->getCode());
        $this->assertEquals('error condition', $error->getMessage());
    }

    public function testResponseShouldBeEmittedAutomaticallyByDefault()
    {
        $this->assertFalse($this->server->getReturnResponse());
    }

    public function testShouldBeAbleToDisableAutomaticResponseEmission()
    {
        $this->testResponseShouldBeEmittedAutomaticallyByDefault();
        $this->server->setReturnResponse(true);
        $this->assertTrue($this->server->getReturnResponse());
    }

    public function testShouldBeAbleToRetrieveSmdObject()
    {
        $smd = $this->server->getServiceMap();
        $this->assertInstanceOf(Server\Smd::class, $smd);
    }

    public function testShouldBeAbleToSetArbitrarySmdMetadata()
    {
        $this->server->setTransport('POST')
                     ->setEnvelope('JSON-RPC-1.0')
                     ->setContentType('application/x-json')
                     ->setTarget('/foo/bar')
                     ->setId('foobar')
                     ->setDescription('This is a test service');

        $this->assertEquals('POST', $this->server->getTransport());
        $this->assertEquals('JSON-RPC-1.0', $this->server->getEnvelope());
        $this->assertEquals('application/x-json', $this->server->getContentType());
        $this->assertEquals('/foo/bar', $this->server->getTarget());
        $this->assertEquals('foobar', $this->server->getId());
        $this->assertEquals('This is a test service', $this->server->getDescription());
    }

    public function testSmdObjectRetrievedFromServerShouldReflectServerState()
    {
        $this->server->addFunction('strtolower')
                     ->setClass(Server\Server::class)
                     ->setTransport('POST')
                     ->setEnvelope('JSON-RPC-1.0')
                     ->setContentType('application/x-json')
                     ->setTarget('/foo/bar')
                     ->setId('foobar')
                     ->setDescription('This is a test service');
        $smd = $this->server->getServiceMap();
        $this->assertEquals('POST', $this->server->getTransport());
        $this->assertEquals('JSON-RPC-1.0', $this->server->getEnvelope());
        $this->assertEquals('application/x-json', $this->server->getContentType());
        $this->assertEquals('/foo/bar', $this->server->getTarget());
        $this->assertEquals('foobar', $this->server->getId());
        $this->assertEquals('This is a test service', $this->server->getDescription());

        $services = $smd->getServices();
        $this->assertInternalType('array', $services);
        $this->assertNotEmpty($services);
        $this->assertArrayHasKey('strtolower', $services);
        $methods = get_class_methods(Server\Server::class);
        foreach ($methods as $method) {
            if ('_' == $method[0]) {
                continue;
            }
            $this->assertArrayHasKey($method, $services);
        }
    }

    public function testHandleValidMethodShouldWork()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->addFunction(__NAMESPACE__ . '\\TestAsset\\FooFunc')
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([true, 'foo', 'bar'])
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isError());


        $request->setMethod(__NAMESPACE__ . '\\TestAsset\\FooFunc')
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isError());
    }

    public function testHandleValidMethodWithNULLParamValueShouldWork()
    {
        $this->server->setClass(__NAMESPACE__ . '\\TestAsset\\Foo')
                     ->addFunction(__NAMESPACE__ . '\\TestAsset\\FooFunc')
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([true, null, 'bar'])
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isError());
    }

    public function testHandleValidMethodWithTooFewParamsShouldPassDefaultsOrNullsForMissingParams()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([true])
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isError());
        $result = $response->getResult();
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals('two', $result[1], var_export($result, 1));
        $this->assertNull($result[2]);
    }

    public function testHandleValidMethodWithTooFewAssociativeParamsShouldPassDefaultsOrNullsForMissingParams()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams(['one' => true])
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isError());
        $result = $response->getResult();
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals('two', $result[1], var_export($result, 1));
        $this->assertNull($result[2]);
    }

    public function testHandleValidMethodWithTooManyParamsShouldWork()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([true, 'foo', 'bar', 'baz'])
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertFalse($response->isError());
        $result = $response->getResult();
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        $this->assertEquals('foo', $result[1]);
        $this->assertEquals('bar', $result[2]);
    }

    public function testHandleShouldAllowNamedParamsInAnyOrder1()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([
                    'three' => 3,
                    'two'   => 2,
                    'one'   => 1
                ])
                ->setId('foo');
        $response = $this->server->handle();
        $result = $response->getResult();

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);
        $this->assertEquals(3, $result[2]);
    }

    public function testHandleShouldAllowNamedParamsInAnyOrder2()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([
                    'three' => 3,
                    'one'   => 1,
                    'two'   => 2,
                ])
                ->setId('foo');
        $response = $this->server->handle();
        $result = $response->getResult();

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);
        $this->assertEquals(3, $result[2]);
    }

    public function testHandleValidWithoutRequiredParamShouldReturnError()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([
                    'three' => 3,
                    'two'   => 2,
                 ])
                ->setId('foo');
        $response = $this->server->handle();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isError());
        $this->assertEquals(Server\Error::ERROR_INVALID_PARAMS, $response->getError()->getCode());
    }

    public function testHandleRequestWithErrorsShouldReturnErrorResponse()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isError());
        $this->assertEquals(Server\Error::ERROR_INVALID_REQUEST, $response->getError()->getCode());
    }

    public function testHandleRequestWithInvalidMethodShouldReturnErrorResponse()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bogus')
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isError());
        $this->assertEquals(Server\Error::ERROR_INVALID_METHOD, $response->getError()->getCode());
    }

    public function testHandleRequestWithExceptionShouldReturnErrorResponse()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('baz')
                ->setId('foo');
        $response = $this->server->handle();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertTrue($response->isError());
        $this->assertEquals(Server\Error::ERROR_OTHER, $response->getError()->getCode());
        $this->assertEquals('application error', $response->getError()->getMessage());
    }

    public function testHandleShouldEmitResponseByDefault()
    {
        $this->server->setClass(TestAsset\Foo::class);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([true, 'foo', 'bar'])
                ->setId('foo');
        ob_start();
        $this->server->handle();
        $buffer = ob_get_clean();

        $decoded = Json\Json::decode($buffer, Json\Json::TYPE_ARRAY);
        $this->assertInternalType('array', $decoded);
        $this->assertArrayHasKey('result', $decoded);
        $this->assertArrayHasKey('id', $decoded);

        $response = $this->server->getResponse();
        $this->assertEquals($response->getResult(), $decoded['result']);
        $this->assertEquals($response->getId(), $decoded['id']);
    }

    public function testResponseShouldBeEmptyWhenRequestHasNoId()
    {
        $this->server->setClass(TestAsset\Foo::class);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([true, 'foo', 'bar']);
        ob_start();
        $this->server->handle();
        $buffer = ob_get_clean();

        $this->assertEmpty($buffer);
    }

    public function testLoadFunctionsShouldLoadResultOfGetFunctions()
    {
        $this->server->setClass(TestAsset\Foo::class);
        $functions = $this->server->getFunctions();
        $server = new Server\Server();
        $server->loadFunctions($functions);
        $this->assertEquals($functions->toArray(), $server->getFunctions()->toArray());
    }

    /**
     * @group ZF-4604
     */
    public function testAddFunctionAndClassThatContainsConstructor()
    {
        $bar = new TestAsset\Bar('unique');

        $this->server->addFunction([$bar, 'foo']);

        $request = $this->server->getRequest();
        $request->setMethod('foo')
            ->setParams([true, 'foo', 'bar'])
            ->setId('foo');
        ob_start();
        $this->server->handle();
        $buffer = ob_get_clean();

        $decoded = Json\Json::decode($buffer, Json\Json::TYPE_ARRAY);

        $this->assertInternalType('array', $decoded);
        $this->assertArrayHasKey('result', $decoded);
        $this->assertArrayHasKey('id', $decoded);
        $this->assertContains('unique', $decoded['result']);

        $response = $this->server->getResponse();
        $this->assertEquals($response->getResult(), $decoded['result']);
        $this->assertEquals($response->getId(), $decoded['id']);
    }

    /**
     * @group 3773
     */
    public function testHandleWithNamedParamsShouldSetMissingDefaults1()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([
                    'two'   => 2,
                    'one'   => 1,
                ])
                ->setId('foo');
        $response = $this->server->handle();
        $result = $response->getResult();

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);
        $this->assertEquals(null, $result[2]);
    }

    /**
     * @group 3773
     */
    public function testHandleWithNamedParamsShouldSetMissingDefaults2()
    {
        $this->server->setClass(TestAsset\Foo::class)
                     ->setReturnResponse(true);
        $request = $this->server->getRequest();
        $request->setMethod('bar')
                ->setParams([
                    'three' => 3,
                    'one'   => 1,
                ])
                ->setId('foo');
        $response = $this->server->handle();
        $result = $response->getResult();

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, $result[0]);
        $this->assertEquals('two', $result[1]);
        $this->assertEquals(3, $result[2]);
    }

    public function testResponseShouldBeInvalidWhenRequestHasLessRequiredParametersPassedWithoutKeys()
    {
        $server = $this->server;
        $server->setClass(TestAsset\FooParameters::class);
        $server->setReturnResponse(true);
        $request = $server->getRequest();
        $request->setMethod('bar')
                ->setParams([true]);
        $server->handle();

        $response = $server->getResponse();
        $this->assertEquals($response->getError()->getCode(), Error::ERROR_INVALID_PARAMS);
    }

    public function testResponseShouldBeInvalidWhenRequestHasLessRequiredParametersPassedWithoutKeys1()
    {
        $server = $this->server;
        $server->setClass(TestAsset\FooParameters::class);
        $server->setReturnResponse(true);
        $request = $server->getRequest();
        $request->setMethod('baz')
                ->setParams([true]);
        $server->handle();
        $response = $server->getResponse();
        $this->assertNotEmpty($response->getError());
        $this->assertEquals($response->getError()->getCode(), Error::ERROR_INVALID_PARAMS);
    }
}

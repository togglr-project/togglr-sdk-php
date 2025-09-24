<?php

declare(strict_types=1);

namespace Togglr\Sdk\Tests;

use PHPUnit\Framework\TestCase;
use Togglr\Sdk\RequestContext;

class RequestContextTest extends TestCase
{
    public function testNewContext(): void
    {
        $context = RequestContext::new();
        $this->assertInstanceOf(RequestContext::class, $context);
        $this->assertEquals([], $context->toArray());
    }

    public function testContextWithData(): void
    {
        $data = ['user.id' => 'user123', 'country' => 'US'];
        $context = new RequestContext($data);
        $this->assertEquals($data, $context->toArray());
    }

    public function testWithUserId(): void
    {
        $context = RequestContext::new()->withUserId('user123');
        $this->assertEquals('user123', $context->get('user.id'));
        $this->assertEquals('user123', $context->get(RequestContext::ATTR_USER_ID));
    }

    public function testWithUserEmail(): void
    {
        $context = RequestContext::new()->withUserEmail('user@example.com');
        $this->assertEquals('user@example.com', $context->get('user.email'));
        $this->assertEquals('user@example.com', $context->get(RequestContext::ATTR_USER_EMAIL));
    }

    public function testWithCountry(): void
    {
        $context = RequestContext::new()->withCountry('US');
        $this->assertEquals('US', $context->get('country_code'));
        $this->assertEquals('US', $context->get(RequestContext::ATTR_COUNTRY_CODE));
    }

    public function testWithDeviceType(): void
    {
        $context = RequestContext::new()->withDeviceType('mobile');
        $this->assertEquals('mobile', $context->get('device_type'));
        $this->assertEquals('mobile', $context->get(RequestContext::ATTR_DEVICE_TYPE));
    }

    public function testWithOs(): void
    {
        $context = RequestContext::new()->withOs('iOS');
        $this->assertEquals('iOS', $context->get('os'));
        $this->assertEquals('iOS', $context->get(RequestContext::ATTR_OS));
    }

    public function testWithOsVersion(): void
    {
        $context = RequestContext::new()->withOsVersion('15.0');
        $this->assertEquals('15.0', $context->get('os_version'));
        $this->assertEquals('15.0', $context->get(RequestContext::ATTR_OS_VERSION));
    }

    public function testWithBrowser(): void
    {
        $context = RequestContext::new()->withBrowser('Safari');
        $this->assertEquals('Safari', $context->get('browser'));
        $this->assertEquals('Safari', $context->get(RequestContext::ATTR_BROWSER));
    }

    public function testWithLanguage(): void
    {
        $context = RequestContext::new()->withLanguage('en-US');
        $this->assertEquals('en-US', $context->get('language'));
        $this->assertEquals('en-US', $context->get(RequestContext::ATTR_LANGUAGE));
    }

    public function testWithAge(): void
    {
        $context = RequestContext::new()->withAge(25);
        $this->assertEquals(25, $context->get('age'));
        $this->assertEquals(25, $context->get(RequestContext::ATTR_AGE));
    }

    public function testWithGender(): void
    {
        $context = RequestContext::new()->withGender('female');
        $this->assertEquals('female', $context->get('gender'));
        $this->assertEquals('female', $context->get(RequestContext::ATTR_GENDER));
    }

    public function testSetCustomAttribute(): void
    {
        $context = RequestContext::new()->set('custom_key', 'custom_value');
        $this->assertEquals('custom_value', $context->get('custom_key'));
    }

    public function testChaining(): void
    {
        $context = RequestContext::new()
            ->withUserId('user123')
            ->withCountry('US')
            ->withDeviceType('mobile')
            ->withOs('iOS')
            ->set('custom', 'value');

        $expected = [
            'user.id' => 'user123',
            'country_code' => 'US',
            'device_type' => 'mobile',
            'os' => 'iOS',
            'custom' => 'value',
        ];

        $this->assertEquals($expected, $context->toArray());
    }

    public function testGetWithDefault(): void
    {
        $context = RequestContext::new();
        $this->assertEquals('default', $context->get('nonexistent', 'default'));
        $this->assertNull($context->get('nonexistent'));
    }

    public function testToString(): void
    {
        $context = RequestContext::new()->withUserId('user123');
        $string = (string) $context;
        $this->assertStringContainsString('RequestContext', $string);
        $this->assertStringContainsString('user.id', $string);
        $this->assertStringContainsString('user123', $string);
    }
}

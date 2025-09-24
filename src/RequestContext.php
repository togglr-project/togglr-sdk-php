<?php

declare(strict_types=1);

namespace Togglr\Sdk;

/**
 * Request context for feature evaluation.
 */
class RequestContext
{
    // Predefined attribute keys
    public const ATTR_USER_ID = 'user.id';
    public const ATTR_USER_EMAIL = 'user.email';
    public const ATTR_USER_ANONYMOUS = 'user.anonymous';
    public const ATTR_COUNTRY_CODE = 'country_code';
    public const ATTR_REGION = 'region';
    public const ATTR_CITY = 'city';
    public const ATTR_MANUFACTURER = 'manufacturer';
    public const ATTR_DEVICE_TYPE = 'device_type';
    public const ATTR_OS = 'os';
    public const ATTR_OS_VERSION = 'os_version';
    public const ATTR_BROWSER = 'browser';
    public const ATTR_BROWSER_VERSION = 'browser_version';
    public const ATTR_LANGUAGE = 'language';
    public const ATTR_CONNECTION_TYPE = 'connection_type';
    public const ATTR_AGE = 'age';
    public const ATTR_GENDER = 'gender';
    public const ATTR_IP = 'ip';
    public const ATTR_APP_VERSION = 'app_version';
    public const ATTR_PLATFORM = 'platform';

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Create a new empty request context.
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * Set the user ID.
     */
    public function withUserId(string $userId): self
    {
        $new = clone $this;
        $new->data[self::ATTR_USER_ID] = $userId;

        return $new;
    }

    /**
     * Set the user email.
     */
    public function withUserEmail(string $email): self
    {
        $new = clone $this;
        $new->data[self::ATTR_USER_EMAIL] = $email;

        return $new;
    }

    /**
     * Set whether the user is anonymous.
     */
    public function withAnonymous(bool $anonymous): self
    {
        $new = clone $this;
        $new->data[self::ATTR_USER_ANONYMOUS] = $anonymous;

        return $new;
    }

    /**
     * Set the country code.
     */
    public function withCountry(string $country): self
    {
        $new = clone $this;
        $new->data[self::ATTR_COUNTRY_CODE] = $country;

        return $new;
    }

    /**
     * Set the region.
     */
    public function withRegion(string $region): self
    {
        $new = clone $this;
        $new->data[self::ATTR_REGION] = $region;

        return $new;
    }

    /**
     * Set the city.
     */
    public function withCity(string $city): self
    {
        $new = clone $this;
        $new->data[self::ATTR_CITY] = $city;

        return $new;
    }

    /**
     * Set the device manufacturer.
     */
    public function withManufacturer(string $manufacturer): self
    {
        $new = clone $this;
        $new->data[self::ATTR_MANUFACTURER] = $manufacturer;

        return $new;
    }

    /**
     * Set the device type.
     */
    public function withDeviceType(string $deviceType): self
    {
        $new = clone $this;
        $new->data[self::ATTR_DEVICE_TYPE] = $deviceType;

        return $new;
    }

    /**
     * Set the operating system.
     */
    public function withOs(string $os): self
    {
        $new = clone $this;
        $new->data[self::ATTR_OS] = $os;

        return $new;
    }

    /**
     * Set the operating system version.
     */
    public function withOsVersion(string $version): self
    {
        $new = clone $this;
        $new->data[self::ATTR_OS_VERSION] = $version;

        return $new;
    }

    /**
     * Set the browser.
     */
    public function withBrowser(string $browser): self
    {
        $new = clone $this;
        $new->data[self::ATTR_BROWSER] = $browser;

        return $new;
    }

    /**
     * Set the browser version.
     */
    public function withBrowserVersion(string $version): self
    {
        $new = clone $this;
        $new->data[self::ATTR_BROWSER_VERSION] = $version;

        return $new;
    }

    /**
     * Set the language.
     */
    public function withLanguage(string $language): self
    {
        $new = clone $this;
        $new->data[self::ATTR_LANGUAGE] = $language;

        return $new;
    }

    /**
     * Set the connection type.
     */
    public function withConnectionType(string $connectionType): self
    {
        $new = clone $this;
        $new->data[self::ATTR_CONNECTION_TYPE] = $connectionType;

        return $new;
    }

    /**
     * Set the user age.
     */
    public function withAge(int $age): self
    {
        $new = clone $this;
        $new->data[self::ATTR_AGE] = $age;

        return $new;
    }

    /**
     * Set the user gender.
     */
    public function withGender(string $gender): self
    {
        $new = clone $this;
        $new->data[self::ATTR_GENDER] = $gender;

        return $new;
    }

    /**
     * Set the IP address.
     */
    public function withIp(string $ip): self
    {
        $new = clone $this;
        $new->data[self::ATTR_IP] = $ip;

        return $new;
    }

    /**
     * Set the application version.
     */
    public function withAppVersion(string $version): self
    {
        $new = clone $this;
        $new->data[self::ATTR_APP_VERSION] = $version;

        return $new;
    }

    /**
     * Set the platform.
     */
    public function withPlatform(string $platform): self
    {
        $new = clone $this;
        $new->data[self::ATTR_PLATFORM] = $platform;

        return $new;
    }

    /**
     * Set an arbitrary key-value pair.
     */
    public function set(string $key, mixed $value): self
    {
        $new = clone $this;
        $new->data[$key] = $value;

        return $new;
    }

    /**
     * Get a value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Convert context to array.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * String representation of the context.
     */
    public function __toString(): string
    {
        return 'RequestContext(' . json_encode($this->data) . ')';
    }
}

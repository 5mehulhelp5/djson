<?php

/**
 * DJson - Dynamic JSON Templating Library
 *
 * @package   Qoliber\DJson
 * @author    Jakub Winkler <jwinkler@qoliber.com>
 * @copyright 2024 Qoliber
 * @license   MIT
 * @link      https://github.com/qoliber/djson
 */

declare(strict_types=1);

namespace Qoliber\DJson\Tests;

use PHPUnit\Framework\TestCase;
use Qoliber\DJson\DJson;

// Test classes for object support
class Product
{
    public string $name;
    private float $price;
    private bool $active;
    private ?string $description;

    public function __construct(string $name, float $price, bool $active = true, ?string $description = null)
    {
        $this->name = $name;
        $this->price = $price;
        $this->active = $active;
        $this->description = $description;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}

class User
{
    private string $username;
    public string $email;
    private ?Profile $profile;

    public function __construct(string $username, string $email, ?Profile $profile = null)
    {
        $this->username = $username;
        $this->email = $email;
        $this->profile = $profile;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }
}

class Profile
{
    private string $fullName;
    public int $age;
    private ?Address $address;

    public function __construct(string $fullName, int $age, ?Address $address = null)
    {
        $this->fullName = $fullName;
        $this->age = $age;
        $this->address = $address;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }
}

class Address
{
    private string $city;
    private string $country;

    public function __construct(string $city, string $country)
    {
        $this->city = $city;
        $this->country = $country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}

class Permission
{
    private bool $canEdit;
    private bool $canDelete;

    public function __construct(bool $canEdit, bool $canDelete)
    {
        $this->canEdit = $canEdit;
        $this->canDelete = $canDelete;
    }

    public function hasEdit(): bool
    {
        return $this->canEdit;
    }

    public function hasDelete(): bool
    {
        return $this->canDelete;
    }
}

class ObjectSupportTest extends TestCase
{
    private DJson $djson;

    protected function setUp(): void
    {
        $this->djson = new DJson();
    }

    public function testObjectPublicProperty(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            'productName' => '{{product.name}}'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('Laptop', $result['productName']);
    }

    public function testObjectGetterMethod(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            'price' => '{{product.price}}'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals(999.99, $result['price']);
    }

    public function testObjectIsMethod(): void
    {
        $product = new Product('Laptop', 999.99, true);

        $template = [
            'isActive' => '{{product.active}}'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertTrue($result['isActive']);
    }

    public function testObjectHasMethod(): void
    {
        $permission = new Permission(true, false);

        $template = [
            'canEdit' => '{{permission.edit}}',
            'canDelete' => '{{permission.delete}}'
        ];

        $result = $this->djson->process($template, ['permission' => $permission]);

        $this->assertTrue($result['canEdit']);
        $this->assertFalse($result['canDelete']);
    }

    public function testNestedObjects(): void
    {
        $address = new Address('Warsaw', 'Poland');
        $profile = new Profile('John Doe', 30, $address);
        $user = new User('johndoe', 'john@example.com', $profile);

        $template = [
            'username' => '{{user.username}}',
            'fullName' => '{{user.profile.fullName}}',
            'age' => '{{user.profile.age}}',
            'city' => '{{user.profile.address.city}}',
            'country' => '{{user.profile.address.country}}'
        ];

        $result = $this->djson->process($template, ['user' => $user]);

        $this->assertEquals('johndoe', $result['username']);
        $this->assertEquals('John Doe', $result['fullName']);
        $this->assertEquals(30, $result['age']);
        $this->assertEquals('Warsaw', $result['city']);
        $this->assertEquals('Poland', $result['country']);
    }

    public function testMixedArrayAndObject(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            'name' => '{{item.name}}',
            'category' => '{{item.category}}'
        ];

        // Array with object inside
        $result = $this->djson->process($template, [
            'item' => [
                'name' => $product,
                'category' => 'Electronics'
            ]
        ]);

        $this->assertInstanceOf(Product::class, $result['name']);
        $this->assertEquals('Electronics', $result['category']);
    }

    public function testObjectInLoop(): void
    {
        $products = [
            new Product('Laptop', 999.99, true),
            new Product('Mouse', 29.99, true),
            new Product('Keyboard', 79.99, false)
        ];

        $template = [
            'products' => [
                '@djson for products as product' => [
                    'name' => '{{product.name}}',
                    'price' => '{{product.price}}',
                    'active' => '{{product.active}}'
                ]
            ]
        ];

        $result = $this->djson->process($template, ['products' => $products]);

        $this->assertCount(3, $result['products']);
        $this->assertEquals('Laptop', $result['products'][0]['name']);
        $this->assertEquals(999.99, $result['products'][0]['price']);
        $this->assertTrue($result['products'][0]['active']);
        $this->assertEquals('Mouse', $result['products'][1]['name']);
        $this->assertFalse($result['products'][2]['active']);
    }

    public function testObjectWithConditional(): void
    {
        $product = new Product('Laptop', 999.99, true);

        $template = [
            '@djson if product.active',
            'status' => 'Available',
            'name' => '{{product.name}}'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('Available', $result['status']);
        $this->assertEquals('Laptop', $result['name']);
    }

    public function testObjectWithUnlessDirective(): void
    {
        $product = new Product('Laptop', 999.99, false);

        $template = [
            '@djson unless product.active',
            'status' => 'Unavailable'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('Unavailable', $result['status']);
    }

    public function testObjectWithFunctions(): void
    {
        $product = new Product('laptop', 999.99);

        $template = [
            'name' => '@djson upper {{product.name}}',
            'price' => '@djson number_format {{product.price}} 2'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('LAPTOP', $result['name']);
        $this->assertEquals('999.99', $result['price']);
    }

    public function testObjectPropertyNotFound(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            'invalid' => '{{product.nonexistent}}'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('', $result['invalid']);
    }

    public function testArrayStillWorks(): void
    {
        $template = [
            'name' => '{{product.name}}',
            'price' => '{{product.price}}'
        ];

        $result = $this->djson->process($template, [
            'product' => [
                'name' => 'Laptop',
                'price' => 999.99
            ]
        ]);

        $this->assertEquals('Laptop', $result['name']);
        $this->assertEquals(999.99, $result['price']);
    }

    public function testMixedNestedArraysAndObjects(): void
    {
        $address = new Address('Warsaw', 'Poland');
        $profile = new Profile('John Doe', 30, $address);

        $template = [
            'username' => '{{data.user.username}}',
            'fullName' => '{{data.user.profile.fullName}}',
            'city' => '{{data.user.profile.address.city}}'
        ];

        $result = $this->djson->process($template, [
            'data' => [
                'user' => [
                    'username' => 'johndoe',
                    'profile' => $profile
                ]
            ]
        ]);

        $this->assertEquals('johndoe', $result['username']);
        $this->assertEquals('John Doe', $result['fullName']);
        $this->assertEquals('Warsaw', $result['city']);
    }

    public function testObjectWithSetDirective(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            '@djson set total = product.price * 2' => [
                'total' => '{{total}}'
            ]
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals(1999.98, $result['total']);
    }

    public function testObjectWithMatchDirective(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            '@djson match product.name' => [
                '@djson case Laptop' => [
                    'category' => 'Electronics'
                ],
                '@djson case Mouse' => [
                    'category' => 'Accessories'
                ],
                '@djson default' => [
                    'category' => 'Other'
                ]
            ]
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('Electronics', $result['category']);
    }

    public function testObjectWithNullProperty(): void
    {
        $product = new Product('Laptop', 999.99, true, null);

        $template = [
            'description' => '@djson default {{product.description}} "No description"'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('No description', $result['description']);
    }

    public function testObjectPublicPropertyDirectAccess(): void
    {
        $user = new User('johndoe', 'john@example.com');

        $template = [
            'email' => '{{user.email}}'
        ];

        $result = $this->djson->process($template, ['user' => $user]);

        $this->assertEquals('john@example.com', $result['email']);
    }

    public function testNestedObjectsWithPublicProperties(): void
    {
        $profile = new Profile('John Doe', 30);
        $user = new User('johndoe', 'john@example.com', $profile);

        $template = [
            'email' => '{{user.email}}',
            'age' => '{{user.profile.age}}'
        ];

        $result = $this->djson->process($template, ['user' => $user]);

        $this->assertEquals('john@example.com', $result['email']);
        $this->assertEquals(30, $result['age']);
    }

    public function testObjectInConditionalExpression(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            '@djson if product.price > 500' => [
                'expensive' => true
            ],
            '@djson else' => [
                'expensive' => false
            ]
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertTrue($result['expensive']);
    }

    public function testObjectInTernaryOperator(): void
    {
        $product = new Product('Laptop', 999.99, true);

        $template = [
            'status' => '{{product.active ? "Available" : "Unavailable"}}'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('Available', $result['status']);
    }

    public function testObjectWithMultipleQuotedParameters(): void
    {
        $product = new Product('Laptop', 999.99);

        $template = [
            'replaced' => '@djson replace {{product.name}} "Laptop" "Desktop Computer"'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('Desktop Computer', $result['replaced']);
    }

    public function testObjectWithComplexQuotedString(): void
    {
        $product = new Product('Test Product', 50.00);

        $template = [
            'formatted' => '@djson default {{product.description}} "This is a long description with spaces"'
        ];

        $result = $this->djson->process($template, ['product' => $product]);

        $this->assertEquals('This is a long description with spaces', $result['formatted']);
    }
}

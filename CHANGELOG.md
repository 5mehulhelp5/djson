# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-11-16

### Added

#### Object Support
- **Full PHP Object Support**: DJson now supports PHP objects in addition to arrays
  - Automatically works with getter methods (`getName()`, `getPrice()`)
  - Supports boolean getter methods (`isActive()`, `isEnabled()`)
  - Supports `has` methods (`hasPermission()`, `hasAccess()`)
  - Falls back to public properties when getter methods don't exist
  - Works with nested objects using dot notation (e.g., `{{user.profile.address.city}}`)

- **Enhanced getValue() Method** (`src/DJson.php`)
  - Added object property access via new `getObjectProperty()` private method
  - Intelligently detects and handles objects vs arrays
  - Maintains backward compatibility with array-based data

- **Enhanced FunctionProcessor** (`src/FunctionProcessor.php`)
  - Updated function parameter parsing to support quoted strings with spaces
  - Improved parameter handling for complex function arguments
  - Added object support to context value resolution
  - Better handling of escaped quotes in function parameters

#### Testing
- **Comprehensive Object Support Test Suite** (`tests/ObjectSupportTest.php`)
  - 531 lines of tests covering all object access patterns
  - Tests for public properties, getter methods, `is*()` methods, and `has*()` methods
  - Nested object access tests
  - Object arrays and loops with objects
  - Conditionals with object properties
  - Functions applied to object properties
  - Mixed array and object data structures

### Technical Details

**Object Property Resolution Order:**
1. Try getter method: `getName()` for property `name`
2. Try `is` method: `isActive()` for property `active`
3. Try `has` method: `hasPermission()` for property `permission`
4. Try direct property access for public properties
5. Return `null` if property not found

**Example Usage:**
```php
class Product {
    public string $name;
    private float $price;

    public function getPrice(): float {
        return $this->price;
    }
}

$product = new Product('Laptop', 999.99);

$template = '{
  "name": "{{product.name}}",
  "price": "{{product.price}}"
}';

$result = $djson->process($template, ['product' => $product]);
// Works seamlessly with object properties and getters
```

### Files Changed
- `src/DJson.php` - Added object property access support (+42 lines)
- `src/FunctionProcessor.php` - Enhanced parameter parsing and object support (+106 lines)
- `tests/ObjectSupportTest.php` - Comprehensive test coverage (+531 lines)

### Backward Compatibility
- ✅ Fully backward compatible with array-based data
- ✅ No breaking changes to existing API
- ✅ Works transparently with existing templates

---

## [1.0.0] - 2025-11-13

### Added - Initial Release

#### Core Features
- **Dynamic JSON Templating**: Template-based JSON generation with directives and variables
- **Variable Interpolation**: `{{variable}}` syntax with dot notation support
- **Directives**:
  - `@djson for ... as ...` - Loop over collections
  - `@djson if` - Conditional inclusion
  - `@djson unless` - Inverse conditional
  - `@djson else` - Else clause for conditionals
  - `@djson exists` - Check if variable exists
  - `@djson match/switch` - Pattern matching
  - `@djson set` - Computed values and expressions

#### Functions
- **25+ Built-in Functions**:
  - String: `upper`, `lower`, `trim`, `slug`, `substr`, `ucfirst`, `ucwords`, `length`
  - Array: `join`, `count`, `implode`, `first`, `last`
  - Math: `round`, `ceil`, `floor`, `abs`, `number_format`
  - Date: `date`, `now`
  - Logic: `default`, `ternary`
- **Function Chaining**: Pipe multiple functions together (e.g., `@djson upper|trim {{name}}`)
- **Custom Functions**: Register your own functions via `registerFunction()`

#### Operators
- **Comparison**: `==`, `!=`, `>`, `<`, `>=`, `<=`
- **Logical**: `&&`, `||`, `!`
- **Arithmetic**: `+`, `-`, `*`, `/`
- **Ternary**: `condition ? true : false`

#### Core Classes
- `DJson` - Main processor class
- `FunctionProcessor` - Function execution and management
- `DirectiveInterface` - Interface for custom directives
- Directives:
  - `ForDirective` - Loop implementation
  - `IfDirective` - Conditional implementation
  - `UnlessDirective` - Inverse conditional
  - `ElseDirective` - Else clause
  - `ExistsDirective` - Existence check
  - `MatchDirective` - Pattern matching
  - `SetDirective` - Computed values

#### Testing
- **Comprehensive Test Suite**:
  - `DJsonV2Test.php` - Core functionality tests
  - `LogicalOperatorsTest.php` - Logical operator tests
  - `MatchSwitchTest.php` - Pattern matching tests
  - `NestedConditionalsTest.php` - Nested conditional tests
  - `NestedLoopsTest.php` - Nested loop tests (4-level deep)
  - `SetDirectiveTest.php` - Computed value tests
  - `TernaryOperatorTest.php` - Ternary operator tests
  - `ValidationTest.php` - Template validation tests

#### Features
- **Type Preservation**: Maintains data types (numbers, booleans, null)
- **Nested Structures**: Unlimited nesting of loops, conditionals, and functions
- **JSON & Array Support**: Process from JSON strings or PHP arrays
- **Custom Directives**: Extensible directive system
- **Validation**: Template validation before processing
- **Error Handling**: Clear error messages for debugging

#### Documentation
- Comprehensive README with examples
- Template examples in `tests/templates/`
- Mutation testing documentation

### Requirements
- PHP >= 8.1
- No external dependencies (core library)

---

[1.1.0]: https://github.com/qoliber/djson/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/qoliber/djson/releases/tag/v1.0.0

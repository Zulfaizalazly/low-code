# Page Builder User Guide

**Version:** 1.0  
**Last Updated:** 20 April 2026

---

## Quick Start

The Page Builder lets you create multi-step forms visually without coding.

### Basic Steps

1. **Add Steps** - Create form sections
2. **Add Fields** - Drag fields from library
3. **Configure** - Set labels, validation, bindings
4. **Preview** - Test the form
5. **Save** - Save and publish

---

## Field Types

### Input Fields
- **Text Input** - Single line text
- **Text Area** - Multi-line text
- **Email** - Email validation
- **Number** - Numeric input
- **Date** - Date picker
- **Time** - Time picker

### Selection Fields
- **Dropdown** - Single selection
- **Radio** - Single choice
- **Checkbox** - Multiple choices
- **Toggle** - On/off switch

### Advanced Fields
- **File Upload** - Document upload
- **Signature** - Digital signature
- **Repeater** - Dynamic list
- **Lookup** - Reference data

---

## Data Binding

### Direct Binding
```
Entity: Customer
Path: name
```

### Formula Binding
```
Mode: formula
Expression: firstName + " " + lastName
```

### Lookup Binding
```
Mode: lookup
Source: products
Display: name
Value: id
```

---

## Validation Rules

- **Required** - Field must have value
- **Min Length** - Minimum characters
- **Max Length** - Maximum characters
- **Pattern** - Regex validation
- **Custom** - Custom validation logic

---

## Best Practices

1. **Keep steps focused** - One topic per step
2. **Use clear labels** - Descriptive field names
3. **Add help text** - Guide users
4. **Test thoroughly** - Try all scenarios
5. **Bind data correctly** - Verify mappings

---

**For detailed guide, see full documentation.**


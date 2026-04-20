# Flow Builder User Guide

**Version:** 1.0  
**Last Updated:** 20 April 2026

---

## Table of Contents

1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Node Types](#node-types)
4. [Building Your First Flow](#building-your-first-flow)
5. [Configuring Nodes](#configuring-nodes)
6. [Connecting Nodes](#connecting-nodes)
7. [Validation](#validation)
8. [Testing Flows](#testing-flows)
9. [Saving and Publishing](#saving-and-publishing)
10. [Keyboard Shortcuts](#keyboard-shortcuts)
11. [Best Practices](#best-practices)
12. [Troubleshooting](#troubleshooting)

---

## Introduction

The Flow Builder is a visual tool for creating business process workflows without writing code. You can drag and drop nodes, connect them, and configure business logic visually.

### What You Can Build

- Customer registration flows
- Approval workflows
- Document generation processes
- Notification sequences
- Complex business logic with branching

---

## Getting Started

### Accessing the Flow Builder

1. Navigate to **Studio** → **Features**
2. Select a feature or create a new one
3. Click **Edit Flow** or **Create New Flow**
4. The Flow Builder canvas will open

### Interface Overview

```
┌─────────────────────────────────────────────────────┐
│  [Save] [Validate] [Simulate] [AI Generate]        │ ← Toolbar
├──────────┬──────────────────────────────────────────┤
│          │                                          │
│  Node    │         Canvas                           │
│  Palette │         (Drag nodes here)                │
│          │                                          │
│  • Trigger│                                         │
│  • Command│                                         │
│  • Decision│                                        │
│  • End    │                                         │
│          │                                          │
├──────────┴──────────────────────────────────────────┤
│  Node Inspector (appears when node selected)        │
└─────────────────────────────────────────────────────┘
```

---

## Node Types

### 1. Trigger Node (Start)

**Purpose:** Entry point for the flow

**When to use:**
- Start of every flow
- User submits a form
- External event occurs

**Configuration:**
- Trigger type (manual, scheduled, event)
- Entry mode (user launch, auto)

**Example:**
```
[Trigger: Form Submitted]
```

---

### 2. Command Node

**Purpose:** Execute a business action

**When to use:**
- Create a customer
- Register a facility
- Record a payment
- Any domain command

**Configuration:**
- Command class (e.g., `RegisterCustomer`)
- Payload mapping (map form fields to command)

**Example:**
```
[Command: Register Customer]
Config:
  command_class: App\Domain\Customer\Commands\RegisterCustomer
  payload_mapping:
    name: form.name
    email: form.email
```

---

### 3. Decision Node

**Purpose:** Branch the flow based on conditions

**When to use:**
- Check if amount > 10000
- Verify user role
- Validate business rules

**Configuration:**
- Decision type (simple, expression)
- Condition (e.g., `amount > 10000`)
- Branches (yes/no or multiple outcomes)

**Example:**
```
[Decision: Amount Check]
Condition: form.amount > 10000
Branches:
  - Yes → [Approval Required]
  - No → [Auto Approve]
```

---

### 4. Approval Node

**Purpose:** Request human approval

**When to use:**
- Manager approval needed
- Multi-level approvals
- Compliance checks

**Configuration:**
- Approver role
- Approval message
- Timeout settings

**Example:**
```
[Approval: Manager Review]
Config:
  approver_role: branch_manager
  message: "Please review this facility request"
```

---

### 5. Notification Node

**Purpose:** Send notifications

**When to use:**
- Email customer
- SMS notification
- System alert

**Configuration:**
- Channel (email, SMS, system)
- Recipient
- Template

**Example:**
```
[Notification: Email Customer]
Config:
  channel: email
  recipient: form.email
  template: customer_welcome
```

---

### 6. Document Node

**Purpose:** Generate documents

**When to use:**
- Create PDF contract
- Generate receipt
- Produce report

**Configuration:**
- Template key
- Data mapping

**Example:**
```
[Document: Generate Contract]
Config:
  template_key: facility_contract
  data_mapping:
    customer_name: nodes.register_customer.output.name
```

---

### 7. GL Action Node

**Purpose:** Post accounting entries

**When to use:**
- Record financial transactions
- Post journal entries
- Update ledgers

**Configuration:**
- Account codes
- Debit/credit amounts
- Transaction type

---

### 8. Formula Node

**Purpose:** Calculate values

**When to use:**
- Calculate interest
- Compute totals
- Apply business formulas

**Configuration:**
- Formula expression
- Input variables
- Output variable name

**Example:**
```
[Formula: Calculate Interest]
Config:
  expression: principal * rate * term / 100
  inputs:
    principal: form.amount
    rate: 0.05
    term: 12
  output: calculated_interest
```

---

### 9. End Node

**Purpose:** Terminate the flow

**When to use:**
- End of every flow path
- Success completion
- Error termination

**Configuration:**
- End type (success, error)
- Final message

---

## Building Your First Flow

### Example: Customer Registration Flow

**Goal:** Register a new customer and send welcome email

**Steps:**

1. **Add Trigger Node**
   - Drag "Trigger" from palette
   - Place at left side of canvas
   - Configure: `trigger_type: manual_entry`

2. **Add Command Node**
   - Drag "Command" from palette
   - Place to the right of Trigger
   - Configure:
     ```
     command_class: App\Domain\Customer\Commands\RegisterCustomer
     payload_mapping:
       name: form.name
       email: form.email
     ```

3. **Add Notification Node**
   - Drag "Notification" from palette
   - Place to the right of Command
   - Configure:
     ```
     channel: email
     recipient: form.email
     template: customer_welcome
     ```

4. **Add End Node**
   - Drag "End" from palette
   - Place at right side
   - Configure: `end_type: success`

5. **Connect Nodes**
   - Click and drag from Trigger's output to Command's input
   - Connect Command to Notification
   - Connect Notification to End

6. **Save**
   - Click "Save" button or press `Ctrl+S`

**Result:**
```
[Trigger] → [Register Customer] → [Send Email] → [End]
```

---

## Configuring Nodes

### Opening Node Inspector

1. Click on any node
2. Inspector panel appears on the right
3. Edit configuration fields
4. Changes auto-save

### Configuration Fields

**Common Fields:**
- **Label:** Display name for the node
- **Description:** Optional notes

**Node-Specific Fields:**
- See [Node Types](#node-types) section for details

### Data Mapping

**Accessing Form Data:**
```
form.field_name
```

**Accessing Previous Node Output:**
```
nodes.node_key.output.field_name
```

**Accessing User Context:**
```
auth.user_id
auth.branch_id
auth.entity_id
```

**Example:**
```
payload_mapping:
  customer_id: nodes.register_customer.output.id
  branch_id: auth.branch_id
  amount: form.amount
```

---

## Connecting Nodes

### Creating Connections

1. **Click and Drag:**
   - Click on node's output handle (right side)
   - Drag to target node's input handle (left side)
   - Release to create edge

2. **Edge Configuration:**
   - Click on edge to configure
   - Set conditions (for decision branches)
   - Add labels

### Edge Types

**Simple Edge:**
- Direct connection
- No conditions

**Conditional Edge:**
- From decision nodes
- Has condition (e.g., `outcome === 'approved'`)

**Example:**
```
[Decision] ─(Yes)→ [Approve]
          └─(No)→ [Reject]
```

---

## Validation

### Running Validation

1. Click **Validate** button
2. System checks:
   - Has trigger node
   - Has end node
   - All nodes connected
   - No orphan nodes
   - Configuration complete
   - No circular dependencies

### Validation Results

**✅ Valid Flow:**
```
✓ Flow is valid
✓ All nodes connected
✓ Configuration complete
```

**❌ Invalid Flow:**
```
✗ Missing end node
✗ Node 'command-1' not connected
✗ Command class not specified
```

### Fixing Errors

1. Read error messages
2. Click on problematic node
3. Fix configuration
4. Re-validate

---

## Testing Flows

### Simulation Mode

1. Click **Simulate** button
2. Enter test payload (JSON):
   ```json
   {
     "name": "John Doe",
     "email": "john@example.com",
     "amount": 5000
   }
   ```
3. Click **Run Simulation**
4. View execution path and outputs

### Simulation Results

**Execution Path:**
```
[Trigger] → [Register Customer] → [Send Email] → [End]
```

**Node Outputs:**
```
register_customer:
  id: 123
  name: "John Doe"
  email: "john@example.com"

send_email:
  status: "sent"
  message_id: "msg_456"
```

### Debugging

- Check each node's output
- Verify data mapping
- Test different scenarios
- Fix issues and re-simulate

---

## Saving and Publishing

### Auto-Save

- Flow auto-saves every 30 seconds
- Look for "Saved" indicator

### Manual Save

- Click **Save** button
- Or press `Ctrl+S` (Windows) / `Cmd+S` (Mac)

### Publishing

1. Validate flow (must be valid)
2. Save flow
3. Go to **Release Center**
4. Submit version for review
5. After approval, publish

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+S` / `Cmd+S` | Save flow |
| `Delete` | Delete selected node/edge |
| `Ctrl+Z` | Undo (coming soon) |
| `Ctrl+Y` | Redo (coming soon) |
| `Space + Drag` | Pan canvas |
| `Scroll` | Zoom in/out |

---

## Best Practices

### 1. Naming Conventions

**Good:**
```
register_customer
send_welcome_email
check_credit_limit
```

**Bad:**
```
node1
command_node
temp
```

### 2. Flow Organization

- Keep flows simple (< 20 nodes)
- Group related nodes
- Use clear labels
- Add descriptions

### 3. Error Handling

- Always have end nodes
- Handle decision branches
- Add error notifications
- Test edge cases

### 4. Data Mapping

- Use descriptive variable names
- Document complex mappings
- Validate data types
- Handle null values

### 5. Testing

- Test with real data
- Try different scenarios
- Verify all branches
- Check error cases

---

## Troubleshooting

### Common Issues

**Issue:** "Flow not saving"
- **Solution:** Check internet connection, refresh page

**Issue:** "Validation fails"
- **Solution:** Read error messages, fix configuration

**Issue:** "Node not connecting"
- **Solution:** Ensure nodes are compatible, check handles

**Issue:** "Simulation fails"
- **Solution:** Check payload format, verify node configuration

**Issue:** "Command not found"
- **Solution:** Verify command class exists, check spelling

### Getting Help

1. Check validation messages
2. Review this guide
3. Contact system administrator
4. Submit support ticket

---

## Next Steps

- [Page Builder Guide](PAGE-BUILDER-GUIDE.md)
- [Node Types Reference](NODE-TYPES-REFERENCE.md)
- [Advanced Patterns](ADVANCED-PATTERNS.md)

---

**Need Help?** Contact support at support@arrahnu.com


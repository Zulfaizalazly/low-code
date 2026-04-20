# Arrahnumation V3 — Technical Implementation Patterns

> **Companion to:** V3-BLUEPRINT.md & V3-MIGRATIONS-REFERENCE.md
> **Purpose:** This document defines the coding standards, service contracts, and execution patterns for developers. It ensures that the system is built with high integrity and follows the "Kernel-led" philosophy.

---

## 1. Command & Event Pattern

In V3, strictly **no business logic** should live in Controllers or Livewire components. Everything must go through a Command.

### 1.1 Command Structure
Every write action is a Command class. It must be self-validating.

```php
namespace App\Domain\Facility\Commands;

use App\Kernel\Contracts\Command;

class CreateFacilityApplication implements Command
{
    public function __construct(
        public int $customerId,
        public string $productCode,
        public array $items,
        public array $nominees = []
    ) {}

    public function rules(): array
    {
        return [
            'customerId' => 'required|exists:customers,id',
            'productCode' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.weight' => 'required|numeric|min:0.01',
        ];
    }
}
```

### 1.2 Command Handler (The "Hard" Gate)
Handlers are wrapped in the Kernel's transaction manager.

```php
namespace App\Domain\Facility\Handlers;

class CreateFacilityHandler
{
    public function handle(CreateFacilityApplication $command)
    {
        return DB::transaction(function () use ($command) {
            // 1. Create Facility record
            // 2. Attach Items
            // 3. Attach Nominees (if any)
            // 4. Record Audit Trail
            // 5. Emit Event
            event(new FacilityCreated($facility));
            
            return $facility;
        });
    }
}
```

---

## 2. Automation Runtime Engine

The `FlowExecutor` is the heart of V3. It does not "run code"; it "executes nodes."

### 2.1 The FlowExecutor Contract
```php
namespace App\Runtime\Automation;

class FlowExecutor
{
    public function execute(ExecutionLog $log)
    {
        $currentNode = $log->getCurrentNode();
        
        try {
            $runner = NodeRunnerFactory::make($currentNode->node_type);
            $result = $runner->run($currentNode, $log->context);
            
            $log->recordNodeSuccess($currentNode, $result);
            
            $nextEdge = EdgeResolver::resolve($currentNode, $result);
            if ($nextEdge) {
                $this->execute($log->moveTo($nextEdge->target_node_id));
            } else {
                $log->finishSuccess();
            }
        } catch (Throwable $e) {
            $log->recordNodeFailure($currentNode, $e);
            $this->handleException($currentNode, $log, $e);
        }
    }
}
```

### 2.2 Node Runner Implementation
Every node type (SMS, Document, GL, etc.) is a dedicated class.

```php
namespace App\Runtime\Automation\Nodes;

class NotificationNodeRunner extends BaseNodeRunner
{
    public function run(FlowNode $node, array $context): NodeResult
    {
        $config = $node->config; // e.g., template_id, recipient_path
        
        $recipient = DataPicker::get($context, $config['recipient_path']);
        $body = TemplateRenderer::render($config['template_id'], $context);
        
        Notification::send($recipient, $body);
        
        return NodeResult::success(['sent_at' => now()]);
    }
}
```

---

## 3. Dynamic Page Architecture

The Branch App's UI is generated at runtime from the `PageDefinition`.

### 3.1 The UI Component Payload (JSON)
What the backend sends to the Livewire `DynamicForm` component:

```json
{
  "page_key": "new-pledge-intake",
  "steps": [
    {
      "key": "customer_info",
      "label": "Customer Details",
      "fields": [
        {
          "key": "ic_number",
          "component": "ic_input",
          "label": "IC Number",
          "required": true,
          "binding": "customer.ic_number"
        }
      ]
    },
    {
      "key": "nominee_step",
      "label": "Nominees",
      "fields": [
        {
          "key": "nominees",
          "component": "nominee_repeater",
          "binding": "facility_nominees"
        }
      ]
    }
  ]
}
```

### 3.2 Dynamic Component Mapping
The Livewire component maps these to Flux UI components:

```blade
@foreach($step['fields'] as $field)
    <x-dynamic-component 
        :component="'v3.fields.' . $field['component']"
        :label="$field['label']"
        wire:model.defer="formData.{{ $field['key'] }}"
    />
@endforeach
```

---

## 4. Permission & Scoping Logic

### 4.1 Scope Resolver
The "Kernel Hard-Stop" for data isolation.

```php
namespace App\Kernel\Scoping;

class ScopeResolver
{
    // Ensures a User can only query data they are scoped to
    public static function apply(Builder $query, User $user)
    {
        $query->where('org_id', $user->org_id);
        
        if (!$user->is_hq_admin) {
            $query->where('branch_id', $user->branch_id);
        }
    }
}
```

### 4.2 Feature Guard
Checks permissions against the *published version* metadata.

```php
namespace App\Kernel\Auth;

class FeatureGuard
{
    public function canAccess(User $user, Feature $feature): bool
    {
        $publishedVersion = $feature->currentVersion();
        
        return $publishedVersion->permissions()
            ->where('role_key', $user->role)
            ->where('can_launch', true)
            ->exists();
    }
}
```

---

## 5. Audit & Traceability Pattern

### 5.1 The Immutable Audit Trail
All domain models must use the `Auditable` trait which hooks into Eloquent events.

```php
trait Auditable {
    public static function bootAuditable() {
        static::updated(function ($model) {
            AuditLog::record(
                action: 'updated',
                subject: $model,
                old: $model->getOriginal(),
                new: $model->getAttributes()
            );
        });
    }
}
```

### 5.2 The Workflow Trace
Every automation run MUST have a trace ID that links:
`UI Submission` → `Command Execution` → `Automation Nodes` → `Final Side Effects`

This allows the **Runtime Monitor** to show:
> "User A submitted Form X at 10:00. This triggered Command Y. Node 'Check LTV' passed. Node 'Send SMS' failed at 10:01 with error 'Provider Timeout'."

---

## 6. Definition of "Technical Readiness"
A feature is technically ready for V3 implementation only if:
1. It has a corresponding **Command** class.
2. It has an **Event** emitted upon success.
3. Its data is stored in a **Structured Table** (as per Migration Reference).
4. All UI components used are in the **V3 Flux Library**.
5. It is registered in the **Registry Pipeline**.

---
*End of Technical Implementation Patterns*

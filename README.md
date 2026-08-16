# Magelearn_CreditMemoExport
Magento 2 Credit Memo Export module with Admin export button, message queue processing, XML generation, configurable export paths, and CLI support.
# Magelearn Credit Memo Export

A Magento 2 learning module that demonstrates how to export Credit Memos asynchronously using **Admin UI, Message Queue, Consumer, XML generation, file export, and export-status tracking**.

## Functionality

The module provides an **Export to Service** button on the Magento Admin Credit Memo view page.

### Admin

Navigate to:

**Sales → Credit Memos → Open a Credit Memo**

The following button is displayed:

> **Export to Service**

Clicking the button:

1. Validates the export configuration.
2. Checks whether the Credit Memo has already been queued/exported.
3. Publishes the Credit Memo ID to the Magento Message Queue.
4. Stores the `published_at` timestamp.
5. Adds an Admin Credit Memo comment.
6. The queue consumer processes the message asynchronously.
7. Credit Memo data is converted to XML.
8. XML is written to the configured export directory.
9. A backup copy is created.
10. `exported_at` is updated after successful export.

A second export attempt is prevented when the Credit Memo has already been queued or exported.

---

## Configuration

Go to:

**Stores → Configuration → Services → Magelearn Export**

Configure:

| Configuration | Description                                     |
| ------------- | ----------------------------------------------- |
| Enabled       | Enable/disable Credit Memo export               |
| Export Path   | Directory where XML files are generated         |
| Backup Path   | Directory where backup XML files are stored     |
| Topic Name    | Message Queue topic used for Credit Memo export |

Example:

```text
Export Path:
var/integration/export/creditmemo

Backup Path:
var/export_creditmemo
```

---

## Message Queue Flow

The module uses Magento's asynchronous message queue architecture:

```text
Admin Credit Memo
       ↓
Export Controller
       ↓
Publisher
       ↓
magelearn.creditmemo_export
       ↓
Queue
       ↓
Consumer
       ↓
Credit Memo Exporter
       ↓
XML Generator
       ↓
Export File
       ↓
Update exported_at
```

The controller does **not** generate the XML directly. It only publishes the Credit Memo ID to the queue.

---

## Console Command

The module provides:

```bash
bin/magento magelearn:export:creditmemo
```

### Export by Credit Memo Increment ID

```bash
bin/magento magelearn:export:creditmemo <increment-id>
```

Example:

```bash
bin/magento magelearn:export:creditmemo 000000123
```

### Export by Date Range

```bash
bin/magento magelearn:export:creditmemo \
    --requested-from="2026-08-01" \
    --requested-to="2026-08-16"
```

### Filter by Store

```bash
bin/magento magelearn:export:creditmemo \
    --store-id=1 \
    --requested-from="2026-08-01" \
    --requested-to="2026-08-16"
```

### Export only Credit Memos not yet exported

```bash
bin/magento magelearn:export:creditmemo \
    --requested-from="2026-08-01" \
    --requested-to="2026-08-16" \
    --not-exported-only
```

### Dry Run

To display matching Credit Memos without exporting:

```bash
bin/magento magelearn:export:creditmemo \
    --requested-from="2026-08-01" \
    --requested-to="2026-08-16" \
    --dry-run
```

---

## Start the Queue Consumer

Check available consumers:

```bash
bin/magento queue:consumers:list
```

Look for:

```text
magelearn.creditmemo_export
```

Start the consumer:

```bash
bin/magento queue:consumers:start magelearn.creditmemo_export
```

For development, you can keep this command running in a terminal while testing exports.

---

## What to Check After Queue Processing

After successfully processing a Credit Memo, verify:

### 1. XML Export

Check:

```text
var/integration/export/creditmemo/
```

Expected:

```text
<creditmemo-file>.xml
```

### 2. Backup

Check:

```text
var/export_creditmemo/
```

A backup XML file should be present.

### 3. Credit Memo Comment

Open the Credit Memo in:

**Sales → Credit Memos → Credit Memo View**

The export comment should be displayed in the Credit Memo history.

### 4. Export Tracking

Check:

```sql
SELECT *
FROM magelearn_creditmemo_export
ORDER BY entity_id DESC;
```

Expected state:

```text
cm_entity_id  | published_at        | exported_at
----------------------------------------------------
123            | 2026-08-16 23:40:00 | 2026-08-16 23:40:05
```

---

# Database Tables

The module creates one custom table:

```text
magelearn_creditmemo_export
```

### Structure

| Field          | Purpose                                              |
| -------------- | ---------------------------------------------------- |
| `entity_id`    | Primary key of the export tracking record            |
| `cm_entity_id` | Magento Credit Memo `entity_id`                      |
| `published_at` | Time when the Credit Memo was published to the queue |
| `exported_at`  | Time when the Credit Memo was successfully exported  |

`cm_entity_id` has a foreign-key relationship with:

```text
sales_creditmemo.entity_id
```

and is indexed for efficient lookup.

---

## Export Tracking Lifecycle

Initially:

```text
cm_entity_id = 123
published_at = NULL
exported_at  = NULL
```

After clicking **Export to Service**:

```text
cm_entity_id = 123
published_at = 2026-08-16 23:40:00
exported_at  = NULL
```

After the queue consumer successfully completes the export:

```text
cm_entity_id = 123
published_at = 2026-08-16 23:40:00
exported_at  = 2026-08-16 23:40:05
```

This tracking prevents a Credit Memo from being queued repeatedly and provides a simple export lifecycle:

```text
Not Published
     ↓
Published / Queued
     ↓
Successfully Exported
```

## Magento Architecture Concepts Demonstrated

This module demonstrates:

* Admin Controller
* Admin UI Block and Template
* ACL
* Dependency Injection
* Service Contracts
* Repository Pattern
* Model / ResourceModel
* Declarative Schema
* Message Queue Publisher
* Queue Consumer
* Virtual Types
* Strategy Pattern
* XML generation
* File export
* Export-state tracking
* CLI Commands
* Asynchronous processing

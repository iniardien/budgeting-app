# Budgeting Schema Design

**Goal:** Add the core budgeting tables for categories, budgets, and transactions to support user-owned income and expense tracking.

## Scope

This design adds three new tables:

- `categories`
- `budgets`
- `transactions`

The existing Laravel `users` table remains unchanged to preserve the default authentication setup already present in the project.

## Table Design

### `categories`

- Belongs to a user
- Stores a category `name`
- Stores a `type` limited in size for values such as `income` or `expense`
- Includes timestamps

### `budgets`

- Belongs to a user
- References a category
- Stores `month`, `year`, and `limit_amount`
- Includes a composite unique constraint on `user_id`, `category_id`, `month`, and `year`
- Includes timestamps

### `transactions`

- Belongs to a user
- References a category
- Stores `amount`, `type`, `date`, and optional `description`
- Includes timestamps

## Relationships

- `categories.user_id` references `users.id`
- `budgets.user_id` references `users.id`
- `budgets.category_id` references `categories.id`
- `transactions.user_id` references `users.id`
- `transactions.category_id` references `categories.id`

All foreign keys use cascading deletes so dependent budgeting data is removed when its owning user or category is deleted.

## Migration Strategy

- Create three separate Laravel migration files
- Use `foreignId()->constrained()->cascadeOnDelete()` for relationships
- Use `decimal(15, 2)` for monetary fields
- Use `string(..., 10)` for `type` columns
- Keep rollback order safe by dropping only the table owned by each migration

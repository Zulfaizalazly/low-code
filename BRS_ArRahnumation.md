# Business Requirements Specification (BRS)
# API Management Ar-Rahnu Islamic Pawnbroking Platform

**Document Version:** 1.0  
**Date:** 27 April 2026  
**Prepared By:** Development Team  
**Classification:** Confidential  

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Overview](#2-system-overview)
3. [Business Function Summary](#3-business-function-summary)
4. [Core Platform Features](#4-core-platform-features)
   - 4.1 Authentication (BF-G8S-AS)
   - 4.2 User Management (BF-G8S-PG)
   - 4.3 Organization Management (BF-G8S-ORG)
   - 4.4 Role & Permission Control (BF-G8S-RBAC)
5. [Ar-Rahnu Domain Features](#5-ar-rahnu-domain-features)
   - 5.1 Customer Management (BF-G8S-CUST)
   - 5.2 Facility / Pledge Management (BF-G8S-FAC)
   - 5.3 Gold Valuation (BF-G8S-VAL)
   - 5.4 Payment Processing (BF-G8S-PAY)
   - 5.5 Vault Management (BF-G8S-VLT)
   - 5.6 Document Generation (BF-G8S-DOC)
   - 5.7 Notification Engine (BF-G8S-NTF)
   - 5.8 Approval Workflow (BF-G8S-APR)
   - 5.9 Accounting & General Ledger (BF-G8S-GL)
   - 5.10 Compliance & AMLA (BF-G8S-CMP)
6. [Studio Platform Features](#6-studio-platform-features)
   - 6.1 Feature Registry & Versioning (BF-G8S-STD-FV)
   - 6.2 Flow Builder / Automation Engine (BF-G8S-STD-FL)
   - 6.3 Page Builder / Form Engine (BF-G8S-STD-PG)
   - 6.4 Rule Engine (BF-G8S-STD-RL)
   - 6.5 Formula Engine (BF-G8S-STD-FM)
   - 6.6 Blueprint Registry (BF-G8S-STD-BP)
   - 6.7 Publish & Release Management (BF-G8S-STD-PUB)
   - 6.8 Scope Override System (BF-G8S-STD-SC)
   - 6.9 AI-Assisted Feature Generation (BF-G8S-STD-AI)
   - 6.10 Simulation & Testing (BF-G8S-STD-SIM)
7. [Runtime & Operations Features](#7-runtime--operations-features)
   - 7.1 Staff Portal (BF-G8S-RT-SP)
   - 7.2 Branch Manager Dashboard (BF-G8S-RT-BD)
   - 7.3 Runtime Monitor (BF-G8S-RT-MON)
   - 7.4 Audit Trail (BF-G8S-RT-AUD)
8. [Pre-Configured Ar-Rahnu Blueprints](#8-pre-configured-ar-rahnu-blueprints)
9. [Data Model Summary](#9-data-model-summary)
10. [Non-Functional Requirements](#10-non-functional-requirements)
11. [Technology Stack](#11-technology-stack)
12. [Appendix: Business Function Reference Table](#12-appendix-business-function-reference-table)

---

## 1. Introduction

### 1.1 Purpose

This Business Requirements Specification (BRS) defines the complete functional and business requirements for the **Management Platform**, specifically tailored for **Ar-Rahnu (Islamic Pawnbroking)** operations. The platform serves as a low-code/no-code feature management system that enables HQ administrators to design, build, test, and deploy operational workflows to branch staff without traditional software development cycles.

### 1.2 Scope

The system covers the end-to-end lifecycle of Ar-Rahnu operations including customer onboarding, gold valuation, pledge creation, payment processing, vault management, document generation, compliance checks, and accounting — all orchestrated through a configurable workflow engine.

### 1.3 Intended Audience

- Business Analysts and Product Owners
- System Architects and Developers
- Compliance Officers and Auditors
- Branch Operations Managers
- HQ IT Administrators

### 1.4 Definitions & Abbreviations

| Term | Definition |
|------|-----------|
| Ar-Rahnu | Islamic pawnbroking scheme based on Shariah principles |
| Marhun | Pledged item (gold article) |
| Ujrah | Safekeeping fee charged to the customer |
| Tawarruq | Commodity Murabahah financing structure |
| Akad | Contractual agreement between parties |
| Surat Pajak | Pledge agreement document |
| SAG | Renewal agreement document |
| AMLA | Anti-Money Laundering Act |
| BNM | Bank Negara Malaysia |
| LTV | Loan-to-Value ratio |
| GL | General Ledger |
| KYC | Know Your Customer |
| PEP | Politically Exposed Person |
| MyKad | Malaysian national identity card |
| OCR | Optical Character Recognition |

---

## 2. System Overview

Arrahnumation V3 is architected as a **multi-layered platform** with clear separation between configuration (Studio), execution (Runtime), and administration (Admin/Branch):

```
┌─────────────────────────────────────────────────────────┐
│                    Arrahnumation v3 Platform                       │
├──────────┬──────────┬──────────────┬────────────────────┤
│  Studio  │  Admin   │   Branch     │   Staff Portal     │
│  (HQ)    │  Panel   │   Dashboard  │   (Teller)         │
├──────────┴──────────┴──────────────┴────────────────────┤
│              Runtime Automation Engine                    │
│  ┌─────────┐ ┌──────────┐ ┌────────┐ ┌──────────────┐  │
│  │Flow Orch│ │Page Load │ │Formula │ │Rule Engine   │  │
│  └─────────┘ └──────────┘ └────────┘ └──────────────┘  │
├─────────────────────────────────────────────────────────┤
│              Domain Layer (CQRS + Events)                │
│  Customer │ Facility │ Payment │ Valuation │ Vault      │
│  Approval │ Document │ Notification │ Accounting │ AMLA │
├─────────────────────────────────────────────────────────┤
│              Data & Infrastructure                       │
│  Database │ Queue │ Cache │ External APIs │ AI Service  │
└─────────────────────────────────────────────────────────┘
```

### 2.1 User Roles

| Role | Workspace | Description |
|------|-----------|-------------|
| `super-admin` | Admin Panel, Studio | Full platform access. Manages entities, branches, staff, and all features. |
| `system-admin` | Studio | Manages feature development, publishing, and monitoring. |
| `feature-developer` | Studio | Designs and builds features using Flow Builder and Page Builder. |
| `branch_manager` | Branch Dashboard | Monitors branch operations, staff activity, feature health, and support tickets. |
| `staff` (Teller) | Staff Portal | Executes published features (pledge intake, renewal, redemption, etc.). |

---

## 3. Business Function Summary

| Code | Function Name | Description |
|------|--------------|-------------|
| BF-G8S-AS | Authentication | Manages identity verification mechanisms for secure, centralized access to the platform. |
| BF-G8S-AS-LMK | Login & Logout | Allows users to register and access the system through secure login and logout mechanisms. |
| BF-G8S-PG | User Management | Manages user accounts, roles, and access permissions to ensure orderly usage control. Allows administrators to update, deactivate, or manage user accounts. |
| BF-G8S-ORG | Organization Management | Manages the organizational hierarchy including entities, branches, departments, regions, and staff assignments. |
| BF-G8S-RBAC | Role & Permission Control | Granular role-based and permission-based access control across all platform features. |
| BF-G8S-CUST | Customer Management | End-to-end customer lifecycle from registration through KYC verification and contact management. |
| BF-G8S-FAC | Facility / Pledge Management | Core Ar-Rahnu operations: pledge creation, renewal, redemption, margin management, and auction. |
| BF-G8S-VAL | Gold Valuation | Real-time gold pricing, weight/purity assessment, and LTV calculation for pledged items. |
| BF-G8S-PAY | Payment Processing | Multi-type payment handling: disbursement, repayment, profit collection, penalties, and refunds. |
| BF-G8S-VLT | Vault Management | Physical custody of pledged gold items with check-in, check-out, and reconciliation. |
| BF-G8S-DOC | Document Generation | Template-based generation of Surat Pajak, SAG, receipts, contracts, and compliance letters. |
| BF-G8S-NTF | Notification Engine | Multi-channel notifications via SMS, email, push, and WhatsApp with delivery tracking. |
| BF-G8S-APR | Approval Workflow | Multi-tier approval system for high-value transactions and sensitive operations. |
| BF-G8S-GL | Accounting & GL | Double-entry journal posting with account code mapping and balance verification. |
| BF-G8S-CMP | Compliance & AMLA | Anti-money laundering checks, PEP screening, sanction list matching, and BNM reporting. |
| BF-G8S-STD | Studio Platform | Low-code feature design, build, test, and deploy platform for HQ administrators. |
| BF-G8S-RT | Runtime & Operations | Execution environment for branch staff with monitoring, audit, and support capabilities. |

---

## 4. Core Platform Features

### 4.1 Authentication (BF-G8S-AS)

**Business Function Code:** BF-G8S-AS  
**Function Name:** Authentication  
**Description:** UTMDigital is responsible for managing identity verification mechanisms for users to access the platform securely and centrally.

#### 4.1.1 Login & Logout (BF-G8S-AS-LMK)

**Business Function Code:** BF-G8S-AS-LMK  
**Function Name:** Login & Logout  
**Description:** Allows users to access the system through secure login and logout mechanisms.

| Req ID | Requirement | Priority |
|--------|------------|----------|
| AS-001 | The system shall provide an SSO Login Modal on the landing page that authenticates users via email and password credentials. | High |
| AS-002 | Upon successful authentication, the system shall redirect users to their designated workspace based on role (Studio, Admin, Branch, or Staff Portal). | High |
| AS-003 | The system shall regenerate the session token upon successful login to prevent session fixation attacks. | High |
| AS-004 | The system shall provide a POST-based logout endpoint that invalidates the session and regenerates the CSRF token. | High |
| AS-005 | The system shall display workspace cards on the landing page (Teller, Manager, Studio, Admin) that trigger the login modal when clicked by unauthenticated users. | Medium |
| AS-006 | The system shall display inline error messages for invalid credentials without exposing whether the email or password was incorrect. | Medium |
| AS-007 | The system shall support a loading state indicator during authentication to prevent duplicate submissions. | Low |

#### 4.1.2 Workspace Routing

| Req ID | Requirement | Priority |
|--------|------------|----------|
| AS-008 | Authenticated users with `super-admin` role shall be routed to `/admin` or `/studio`. | High |
| AS-009 | Authenticated users with `branch_manager` role shall be routed to `/branch`. | High |
| AS-010 | Authenticated users with `staff` role shall be routed to `/portal/operations/new-pledge`. | High |
| AS-011 | Authenticated users with `feature-developer` role shall be routed to `/studio`. | High |

---

### 4.2 User Management (BF-G8S-PG)

**Business Function Code:** BF-G8S-PG  
**Function Name:** User Management  
**Description:** Manages user accounts, roles, and access permissions within the system to ensure orderly usage control. Allows administrators to update, deactivate, or manage user accounts.

| Req ID | Requirement | Priority |
|--------|------------|----------|
| PG-001 | The system shall maintain user profiles with: name, email, employee number, phone, avatar, active status, join date, and leave date. | High |
| PG-002 | The system shall support user activation and deactivation without deleting the account record. | High |
| PG-003 | The system shall associate each user with an organizational entity (`entity_id`). | High |
| PG-004 | The system shall track primary and secondary staff assignments linking users to branches and departments. | High |
| PG-005 | The system shall provide a Staff Manager interface (`/admin/staff`) for viewing and managing all staff records. | High |
| PG-006 | The system shall provide a User Role Manager interface (`/admin/users/{user}/roles`) for assigning and revoking roles per user. | High |
| PG-007 | The system shall support password hashing using Laravel's built-in hashing mechanism. | High |
| PG-008 | The system shall provide CLI commands for permission management: `permission:manage list-roles`, `list-permissions`, `assign-role`, `create-role`, `show-user`. | Medium |

---

### 4.3 Organization Management (BF-G8S-ORG)

**Business Function Code:** BF-G8S-ORG  
**Function Name:** Organization Management  
**Description:** Manages the organizational hierarchy including legal entities, physical branches, departments, regions, and staff assignments.

| Req ID | Requirement | Priority |
|--------|------------|----------|
| ORG-001 | The system shall support multi-entity architecture where each entity represents a legal organization (e.g., BR, MBSB). | High |
| ORG-002 | The system shall provide a Branch Manager interface (`/admin/branches`) for creating, editing, activating, and deactivating branches. | High |
| ORG-003 | The system shall provide a Branch Detail view (`/admin/branches/{branch}`) showing branch information, assigned staff, and operational statistics. | High |
| ORG-004 | The system shall support branch categorization by type with breakdown statistics on the Admin Dashboard. | Medium |
| ORG-005 | The system shall provide a Department Manager interface (`/admin/departments`) for managing organizational departments. | High |
| ORG-006 | The system shall support regional grouping of branches via a Region model. | Medium |
| ORG-007 | The system shall track staff assignments with: user, branch, department, employment type, start date, end date, and primary flag. | High |
| ORG-008 | The system shall provide an Entity Settings interface (`/admin/entity`) for configuring entity-level parameters. | Medium |
| ORG-009 | The Admin Dashboard shall display: active branch count, total staff count, department count, region count, branch type breakdown, employment type breakdown, and recent assignments. | High |

---

### 4.4 Role & Permission Control (BF-G8S-RBAC)

**Business Function Code:** BF-G8S-RBAC  
**Function Name:** Role & Permission Control  
**Description:** Provides granular role-based and permission-based access control across all platform workspaces and features.

| Req ID | Requirement | Priority |
|--------|------------|----------|
| RBAC-001 | The system shall implement role-based access control using Spatie Laravel Permission with the following default roles: `super-admin`, `system-admin`, `feature-developer`, `branch_manager`, `hq-admin`, `staff`. | High |
| RBAC-002 | The system shall enforce role checks via `CheckRole` middleware on all workspace route groups. | High |
| RBAC-003 | The system shall enforce granular permission checks via `CheckPermission` middleware on individual routes. | High |
| RBAC-004 | Permissions shall be organized into categories: Features, Flows, Pages, Versions, Scopes, Audit & Monitoring, Users, AI, and Runtime. | High |
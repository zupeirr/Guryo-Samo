# Guryo Samo — Real Estate Management System

> **"Guryo Samo"** is a Somali phrase meaning *"Your Home."* The system is a web-based Real Estate Management platform developed to serve local property businesses in Somaliland, offering both a public-facing property portal and a secured administrative control panel.

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Problem Statement](#2-problem-statement)
3. [Project Objectives](#3-project-objectives)
   - [3.1 General Objective](#31-general-objective)
   - [3.2 Specific Objectives](#32-specific-objectives)
4. [Scope and Limitations](#4-scope-and-limitations)
   - [4.1 Scope](#41-scope)
   - [4.2 Limitations](#42-limitations)
5. [System Specifications](#5-system-specifications)
   - [5.1 Functional Requirements](#51-functional-requirements)
   - [5.2 Non-Functional Requirements](#52-non-functional-requirements)
   - [5.3 Hardware Requirements](#53-hardware-requirements)
   - [5.4 Software Requirements](#54-software-requirements)
6. [Project Feasibility](#6-project-feasibility)
   - [6.1 Technical Feasibility](#61-technical-feasibility)
   - [6.2 Economic Feasibility](#62-economic-feasibility)
   - [6.3 Operational Feasibility](#63-operational-feasibility)
   - [6.4 Schedule Feasibility](#64-schedule-feasibility)
7. [Methodology](#7-methodology)
8. [Results and Outcomes](#8-results-and-outcomes)
9. [Conclusion and Recommendation](#9-conclusion-and-recommendation)

---

## 1. Introduction

The rapid expansion of urban areas and the rising demand for residential and commercial properties in Somaliland have created a pressing need for modern, technology-driven solutions in the local real estate sector. Despite this growth, the operational practices of most real estate agencies in the region remain largely manual and fragmented — relying on informal communication channels, paper-based records, and unstructured digital tools such as social media groups and messaging applications.

This project presents **Guryo Samo**, a web-based Real Estate Management System developed as an academic capstone project to address the aforementioned operational deficiencies. The name *Guryo Samo*, meaning "Your Home" in Somali, reflects the system's primary purpose: to connect property seekers with available listings in a transparent, organized, and accessible digital environment.

Guryo Samo is a dual-sided platform consisting of a **public-facing property portal**, through which visitors and registered customers can browse, search, and inquire about property listings, and a **secured administrative control panel**, through which authorized staff manage all aspects of the system — from property listings and customer messages to user accounts, analytics, and site configuration.

The system is built entirely using open-source technologies — PHP, MySQL, HTML5, CSS3, and vanilla JavaScript — without dependency on commercial frameworks, making it both cost-effective and fully comprehensible for academic evaluation and professional demonstration. It is designed to be deployable on standard shared hosting infrastructure that is widely available and affordable in the Somaliland context.

This document serves as the formal academic report for the Guryo Samo project. It presents the problem statement, project objectives, system scope and limitations, technical specifications, feasibility analysis, development methodology, and a comprehensive assessment of results and outcomes.

---

## 2. Problem Statement

The real estate sector in Somaliland continues to experience significant growth; however, the majority of local property agencies still rely on informal and manual methods to manage, advertise, and communicate property listings. These methods — including the use of social media messaging, printed flyers, and verbal referrals — are inherently inefficient, difficult to scale, and prone to information loss or inconsistency.

Property seekers face considerable difficulty locating relevant listings, as there is no centralized, searchable platform tailored to the local market. Similarly, real estate agents and business owners lack a systematic tool for recording, updating, and monitoring their portfolios of properties. The absence of a structured digital solution results in delayed transactions, poor customer engagement, and an overall reduction in operational efficiency.

Furthermore, existing commercial real estate platforms (such as Zillow or Bayut) are not localized for the Somaliland context, lack support for local market conventions, and incur recurring subscription costs that are economically prohibitive for small to mid-scale local agencies.

This project addresses the identified gap by proposing the development of **Guryo Samo**, a locally developed, web-based Real Estate Management System that provides a streamlined digital environment for both property managers and prospective clients.

---

## 3. Project Objectives

### 3.1 General Objective

The general objective of this project is to **design and develop a fully functional, web-based Real Estate Management System** that enables local real estate businesses in Somaliland to digitally manage property listings, facilitate communication with prospective clients, and provide the public with an accessible, searchable online property portal.

### 3.2 Specific Objectives

The following specific objectives were identified to guide the development process toward the general objective:

1. **To design and implement a structured relational database** capable of storing and managing property records, user accounts, contact messages, administrative settings, and related transactional data in an organized and retrievable manner.

2. **To develop a public-facing web interface** that allows visitors to browse, search, and filter available property listings by parameters such as location, property type, listing status, and maximum price, and to view detailed information about individual properties.

3. **To build a secure, role-based administrative dashboard** that restricts access based on authenticated user roles (admin, staff, customer), and enables authorized personnel to perform full Create, Read, Update, and Delete (CRUD) operations on property listings.

4. **To implement a secure user authentication and authorization system** using industry-standard practices, including bcrypt password hashing, PHP session management, session fixation prevention, and CSRF token-based form protection.

5. **To integrate a functional contact and messaging module** that allows visitors to submit property inquiries through a web form, with all submitted messages stored in the database and made accessible to administrative users for review.

6. **To enforce robust input validation and security mechanisms** throughout the system, including prepared SQL statements to prevent injection attacks, output escaping to prevent Cross-Site Scripting (XSS), and MIME-type verification for file uploads to prevent malicious file execution.

7. **To provide an administrative reporting module** that aggregates and displays key business metrics, including property count by type and status, price statistics, top locations, and user and message summaries, to support data-informed decision-making.

---

## 4. Scope and Limitations

### 4.1 Scope

The scope of the Guryo Samo system encompasses the following components and functionalities:

**Public Portal:**
- A homepage featuring a hero section with an integrated property search form, real-time statistics (total listings, for-sale count, for-rent count), and a display of featured properties.
- A properties listing page with multi-parameter filtering (location keyword, property type, listing status, and maximum price) and server-side pagination.
- A property detail page presenting full listing specifications, the primary property image, and a section displaying similar listings.
- An About Us page presenting company information including mission, vision, and team.
- A contact page featuring a validated submission form that stores messages in the database and, when configured, delivers an email notification to the administrator via SMTP.
- A public registration page allowing visitors to create customer-level accounts.
- A unified login page for all user roles, with automatic role-based redirection upon successful authentication.

**Administrative Panel (`/admin`):**
- A secure dashboard displaying summary statistics and recent activity (latest properties added, most recent messages received).
- Full CRUD management of property listings, including support for primary image upload with MIME-type validation and random filename generation.
- Management of customer contact messages, with read/unread status tracking and the ability to delete records.
- A user management interface allowing administrators to view all registered accounts and remove customer accounts, with admin and staff accounts protected from deletion.
- An analytics and reporting module presenting property distributions by type and status, price statistics, top-performing locations, and message and user summaries.
- A site configuration settings panel allowing administrators to manage site name, SMTP email credentials, notification preferences, pagination limits, and maintenance mode.
- Support for additional administrative modules including agent management, appointments, transactions, commissions, leads, activity logs, and system notifications.

**Security Scope:**
- Protection against SQL Injection via MySQLi prepared statements and parameterized queries.
- Protection against Cross-Site Scripting (XSS) via `htmlspecialchars()` output escaping on all user-supplied data.
- Protection against Cross-Site Request Forgery (CSRF) via cryptographically random token verification on all state-modifying form submissions.
- Protection against session fixation via `session_regenerate_id()` upon login.
- Protection against malicious file uploads via `finfo`-based MIME type verification, file size limits (5 MB maximum), and randomized filename generation.
- Role-based access control enforced on every administrative page via the `requireLogin()` gate function.

### 4.2 Limitations

The following limitations apply to the current version of the system:

1. **Absence of a dedicated customer-facing dashboard:** While users may register and authenticate as customers, the current version does not provide customer-specific features such as saved searches, property favorites, or a personal inquiry history. The registration feature is functional but does not yet unlock meaningful customer-specific functionality.

2. **Single image per property listing:** The system currently supports only one primary image per property. A multi-image gallery feature (requiring a `property_media` junction table and a gallery upload interface) is partially designed but not yet fully integrated into the public-facing UI.

3. **No rate limiting on the authentication endpoint:** The login form is not currently protected against automated brute-force attacks. Implementing a login attempt counter with a time-based lockout mechanism is identified as a future improvement.

4. **No environment variable configuration:** Database credentials and application configuration constants are stored directly in `config/db.php`. For production deployments, these should be moved to server-level environment variables or a `.env` file to reduce the risk of credential exposure.

5. **No HTTPS enforcement in code:** The system assumes that the deployment environment provides TLS/SSL. HTTPS redirection via `.htaccess` is recommended but not included by default in the current release.

6. **No property-specific inquiry module:** Visitors can submit a general contact message but cannot attach an inquiry to a specific property listing. A targeted inquiry feature linked to individual property IDs is planned but not yet implemented.

7. **No advanced sorting on the public listings page:** Properties can be filtered but cannot be sorted by user-selected criteria such as price ascending or descending, or by listing date.

8. **XAMPP-based local deployment assumed:** The system is configured for local development using XAMPP (Apache + MySQL + PHP). A production deployment requires additional hardening steps as documented in the setup guide.

---

## 5. System Specifications

### 5.1 Functional Requirements

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-01 | The system shall allow any visitor to browse all active property listings on the public portal without requiring authentication. | High |
| FR-02 | The system shall allow visitors to filter property listings by location (partial text match), property type (Apartment, Villa, House, Studio, Office, Land), listing status (For Sale, For Rent), and maximum price. | High |
| FR-03 | The system shall display paginated results on the properties listing page, with the number of results per page configurable via the admin settings panel. | High |
| FR-04 | The system shall display a detailed view of any individual property, including its title, type, price, location, description, bedrooms, bathrooms, floor size, status, and primary image. | High |
| FR-05 | The system shall allow visitors to submit contact messages via the contact form. All submitted messages shall be stored in the database with an unread status. | High |
| FR-06 | The system shall allow new users to register for a customer-level account by providing a full name, unique username, unique email address, and a confirmed password. | High |
| FR-07 | The system shall authenticate all users (admin, staff, customer) through a single login page using username and bcrypt-verified password credentials. | High |
| FR-08 | Upon successful login, the system shall redirect admin and staff users to the administrative dashboard, and customer users to the public homepage. | High |
| FR-09 | The system shall prevent unauthenticated and customer-role users from accessing any page within the `/admin` directory. | High |
| FR-10 | The system shall allow authorized administrative users to add new property listings, including a title, type, price, location, description, bedroom and bathroom count, size, status, and a primary image upload. | High |
| FR-11 | The system shall allow authorized administrative users to edit any existing property listing, with the option to replace the current image. | High |
| FR-12 | The system shall allow authorized administrative users to permanently delete a property listing, along with the associated uploaded image file from the server. | High |
| FR-13 | The system shall allow administrators to view all submitted customer messages, mark messages as read, and delete message records. | Medium |
| FR-14 | The system shall allow administrators to view all registered user accounts and delete customer-level accounts. Admin and staff accounts shall be protected from deletion. | Medium |
| FR-15 | The system shall provide an analytics and reporting page presenting property counts by type and status, price statistics, top locations, and user and message counts. | Medium |
| FR-16 | The system shall provide a settings management interface allowing administrators to configure site name, SMTP credentials, notification preferences, pagination size, and maintenance mode. | Medium |

### 5.2 Non-Functional Requirements

| ID | Category | Requirement |
|----|----------|-------------|
| NFR-01 | Security | All database queries involving user-supplied input shall use MySQLi prepared statements with parameterized binding to prevent SQL injection. |
| NFR-02 | Security | All user-supplied data rendered in HTML output shall be escaped using `htmlspecialchars()` with `ENT_QUOTES` and UTF-8 encoding to prevent XSS attacks. |
| NFR-03 | Security | All state-modifying HTTP POST requests shall be verified against a server-side CSRF token using `hash_equals()` for timing-attack-safe comparison. |
| NFR-04 | Security | All passwords shall be stored exclusively as bcrypt hashes generated by PHP's `password_hash()` function. Plain-text passwords shall never be persisted. |
| NFR-05 | Security | All file uploads shall be validated by MIME type using the `finfo` PHP extension, restricted to allowlisted image types, limited to 5 MB, and saved under a randomly generated filename. |
| NFR-06 | Usability | The public interface shall be responsive and render correctly on desktop and mobile viewport sizes. |
| NFR-07 | Usability | The system shall provide informative success and error feedback messages to users after all form submissions. |
| NFR-08 | Performance | Property listing pages shall query only the rows required for the current page using SQL `LIMIT` and `OFFSET` pagination, avoiding full-table retrieval of large datasets. |
| NFR-09 | Maintainability | All pages shall share centralized includes for the database connection (`config/db.php`), helper functions (`includes/functions.php`), and layout partials to minimize code duplication. |
| NFR-10 | Compatibility | The system shall function correctly on PHP version 7.4 or higher and MySQL 5.7 or higher, as required by `password_hash()`, `random_bytes()`, and `finfo_open()`. |
| NFR-11 | Reliability | The system shall gracefully handle missing or invalid property IDs on the detail page by displaying a user-friendly error message rather than crashing or leaking internal state. |

### 5.3 Hardware Requirements

| Component | Minimum Specification | Recommended |
|-----------|----------------------|-------------|
| Processor | Dual-core 1.5 GHz | Quad-core 2.4 GHz or higher |
| RAM | 2 GB | 4 GB or higher |
| Storage | 500 MB (application) + space for uploads | 10 GB+ for media storage at scale |
| Network | Any broadband internet connection | Stable broadband >= 10 Mbps for server deployment |
| Display | 1024 x 768 resolution (client browser) | 1280 x 720 or higher |

> For **local development**, a personal computer running XAMPP meets all hardware requirements. For **production deployment**, a shared hosting plan with at least 512 MB RAM and a MySQL-enabled account is sufficient for the current scale of the application.

### 5.4 Software Requirements

| Category | Software | Version | Purpose |
|----------|----------|---------|---------|
| Backend Language | PHP | 7.4 or higher | Server-side scripting, session management, database interaction |
| Database | MySQL | 5.7 or higher | Relational data storage (users, properties, messages, settings) |
| Database Driver | MySQLi (OOP) | Bundled with PHP | PHP-to-MySQL communication via object-oriented interface |
| Web Server | Apache HTTP Server | 2.4 or higher | Serving PHP pages and static assets |
| Local Dev Environment | XAMPP | Any recent version | Bundles Apache + MySQL + PHP for local development |
| Email Library | PHPMailer | 6.x (bundled) | Reliable SMTP-based email delivery for contact notifications |
| Client Browser | Any modern browser | Chrome 90+, Firefox 88+, Edge 90+, Safari 14+ | Rendering the public and admin interfaces |
| Code Editor | VS Code (or equivalent) | Any | Development and editing of PHP, CSS, and JavaScript files |
| Database Management | phpMyAdmin | Bundled with XAMPP | GUI-based database administration during development |
| Version Control | Git | Any | Source code versioning and collaboration |

---

## 6. Project Feasibility

### 6.1 Technical Feasibility

The technical feasibility of Guryo Samo is assessed as **high**. All technologies employed in this project — PHP, MySQL, Apache, HTML, CSS, and vanilla JavaScript — are mature, extensively documented, and widely supported across a broad range of hosting environments. The development team possesses the foundational knowledge required to implement, test, and debug a PHP/MySQL web application without requiring specialized or proprietary tooling.

The choice to use a server-side rendered (SSR) architecture with PHP, rather than a decoupled API and JavaScript frontend framework, significantly reduces architectural complexity. Each page is a self-contained PHP file that manages its own database queries, business logic, and HTML output generation — an approach that is straightforward to implement, debug, and demonstrate.

The use of XAMPP for local development eliminates the dependency on external infrastructure during the building and testing phases. All required components (Apache, MySQL, PHP, phpMyAdmin) are bundled and configurable with minimal setup effort.

Security-critical features including prepared statements, bcrypt hashing, CSRF protection, and MIME-type validation for file uploads are implemented using PHP's standard library functions, requiring no external security dependencies beyond PHPMailer for email delivery. This ensures that the system's security posture is both achievable within the project's technical scope and reproducible in any standard PHP hosting environment.

### 6.2 Economic Feasibility

The project is assessed as **economically feasible**, particularly in the context of a university-level academic project and early-stage deployment for a small local business.

The entire technology stack is free and open-source:
- PHP is distributed under the PHP License, free for commercial use.
- MySQL Community Edition is free under the GNU General Public License.
- Apache HTTP Server is distributed under the Apache License 2.0.
- PHPMailer is distributed under the LGPL 2.1 license, free for use.
- XAMPP is freely available for all platforms.

**Development cost** is limited to developer time, as no software licenses, API subscriptions, or proprietary framework fees are required.

**Deployment cost** for a production environment is minimal. Shared hosting plans supporting PHP and MySQL are available from providers such as Hostinger or Namecheap at approximately USD $3–$10 per month, making the system economically accessible for small real estate businesses operating in the Somaliland market.

As the system is self-hosted, there are no recurring per-listing fees or third-party commission costs — unlike commercial listing platforms. This positions Guryo Samo as a cost-effective long-term alternative to subscription-based real estate platforms.

### 6.3 Operational Feasibility

The operational feasibility of the system is assessed as **high** for the target user group.

**For administrative users (admin/staff):** The administrative dashboard is designed around familiar web-based content management conventions, with clearly labeled navigation, tabular data displays, and form-based data entry. No specialized technical knowledge is required to add, edit, or delete property listings, read contact messages, or generate reports. Staff can be onboarded with a brief walkthrough of the admin panel.

**For public users (visitors/customers):** The public interface mirrors the interaction patterns of widely-used property search websites, with a prominent search form, card-based listing layout, and detail pages following standard conventions. The learning curve for end users is effectively zero for basic browsing tasks.

**For system maintenance:** The codebase is organized in a logical directory structure with shared includes, a single database connection file, and a centralized helper function library. Any developer familiar with PHP and MySQL can understand, modify, and extend the codebase without requiring a project-specific onboarding period of significant duration.

**Organizational readiness:** Local real estate businesses in Somaliland that currently rely on manual or social-media-based listing methods are operationally ready to transition to a web-based system. The system requires only a computer with an internet browser on the client side — no installation, no mobile application, and no specialized hardware.

### 6.4 Schedule Feasibility

The development of Guryo Samo was planned and executed within the timeline constraints of an academic semester. The following high-level schedule was observed:

| Phase | Activities | Estimated Duration |
|-------|-----------|-------------------|
| Phase 1 — Requirements & Planning | Problem definition, feasibility analysis, system specification, database schema design | 1–2 weeks |
| Phase 2 — Database & Backend Foundation | Setting up XAMPP, creating the database schema, implementing `config/db.php`, `includes/functions.php`, authentication system | 1–2 weeks |
| Phase 3 — Public Frontend Development | Building all public pages with styling and client-side JavaScript | 2–3 weeks |
| Phase 4 — Admin Dashboard Development | Implementing admin CRUD for properties, messages management, user management, reports, and settings panel | 2–3 weeks |
| Phase 5 — Security Hardening & Testing | Integrating CSRF protection, input validation, file upload validation, access control verification, and cross-browser testing | 1 week |
| Phase 6 — Documentation & Presentation | Writing technical documentation, preparing the project guide, and finalizing for academic submission | 1 week |
| **Total Estimated Duration** | | **8–12 weeks** |

This timeline is consistent with a standard academic project cycle. The use of well-understood technologies without external framework dependencies contributed to schedule predictability and the avoidance of significant integration delays.

---

## 7. Methodology

The development of Guryo Samo followed an **incremental and iterative software development approach**, drawing from principles of the **Agile methodology** adapted for a solo academic project context. Rather than a rigid waterfall sequence, development proceeded through overlapping phases where completed components were tested and integrated before the next layer was built, allowing early detection of design issues and continuous refinement.

### Phase 1 — Requirements Elicitation and Analysis

The initial phase focused on identifying the stakeholders (real estate business owners, administrative staff, and prospective property buyers), defining the problem domain, and documenting functional and non-functional requirements. The user roles (admin, staff, customer, and guest visitor) and their respective permissions were specified before any code was written. This phase produced the system's database schema design and a functional decomposition of the required modules.

### Phase 2 — Database Design and Schema Implementation

A relational data model was designed to represent the core entities of the system: `users`, `properties`, `messages`, `settings`, and related entities (`property_media`, `agents`, `appointments`, `transactions`, `leads`, `notifications`, `activity_logs`). The schema was implemented in SQL (`database/real_estate.sql`) using the InnoDB storage engine with UTF-8 (utf8mb4) character encoding to support multilingual content. Seed data including a default admin account and sample properties was included to enable immediate testing upon database import.

### Phase 3 — Backend Architecture and Core Libraries

The foundational backend infrastructure was established prior to building individual pages. This included:

- `config/db.php` — A centralized database connection module using MySQLi in object-oriented mode.
- `includes/functions.php` — A shared library of helper functions encapsulating session management, output sanitization (`clean()`), price formatting (`formatPrice()`), authentication checks (`isLoggedIn()`, `isAdminLoggedIn()`), access control (`requireLogin()`), CSRF token generation and verification (`csrfToken()`, `verifyCsrf()`), file upload handling (`uploadPropertyImage()`), pagination (`paginate()`, `renderPagination()`), and settings retrieval (`getSetting()`).

This architecture ensures that all pages share the same validated, security-tested utilities, reducing the likelihood of per-page implementation errors.

### Phase 4 — Frontend and Backend Page Development

Public-facing pages and admin pages were developed concurrently in cycles. Each page was built following a consistent structural pattern:

**Public pages pattern:**
```
(1) Require config/db.php          -> open database connection
(2) Require includes/functions.php -> load session and helpers
(3) PHP business logic             -> query DB, process GET/POST
(4) Include includes/header.php    -> HTML head and navbar
(5) HTML output with PHP data      -> page content
(6) Include includes/footer.php    -> close HTML document
```

**Admin pages pattern:**
```
(1) Require ../config/db.php
(2) Require ../includes/functions.php
(3) requireLogin()                 -> redirect if not authenticated as admin/staff
(4) PHP business logic
(5) Include admin-header.php       -> includes sidebar navigation
(6) HTML/PHP content output
(7) Include admin-footer.php
```

Dynamic SQL query construction with prepared statements was used on all data-retrieval pages to support filtered, paginated queries without SQL injection risk.

### Phase 5 — Security Integration and Validation

Security was not treated as a final phase but was integrated throughout development. Each form includes CSRF tokens; all outputs are escaped; all queries use prepared statements; all file uploads are MIME-verified. Validation is implemented in four layers:

1. **HTML attributes** (`required`, `type="email"`, `min`) — client-side hint, easily bypassed.
2. **JavaScript** (`main.js`) — pre-submission UX validation for instant user feedback.
3. **PHP server-side** — authoritative, bypass-proof validation that always runs on the server.
4. **Database constraints** (`ENUM`, `NOT NULL`, `UNIQUE`) — final enforcement layer at the data tier.

### Phase 6 — Testing and Debugging

Testing was conducted manually across all functional paths. For each module, both the happy path (valid inputs, successful outcomes) and edge cases (missing fields, invalid types, unauthorized access attempts, malformed file uploads) were tested. PHP's `var_dump()`, `print_r()`, and the Apache error log were used to diagnose and resolve issues during development. Browser DevTools were used to inspect HTTP requests, responses, cookies, and JavaScript errors.

### Phase 7 — Documentation

Technical documentation was produced throughout development, culminating in a comprehensive project guide (`guryo_samo_complete_guide.md`) covering architecture, database schema, code logic, security measures, local setup instructions, deployment guidance, and a Q&A section for academic defense preparation. This README serves as the formal academic project report.

---

## 8. Results and Outcomes

The development of Guryo Samo produced a fully functional, locally deployable Real Estate Management System that satisfies the stated project objectives. The following outcomes were achieved:

### 8.1 Delivered System Components

| Module | Status | Description |
|--------|--------|-------------|
| Public Homepage | Complete | Hero search bar, live property statistics, featured listings grid |
| Properties Listing | Complete | Multi-parameter filtering, server-side pagination |
| Property Detail View | Complete | Full specs, primary image, similar listings |
| About & Contact Pages | Complete | Company info, validated contact form with database storage |
| User Registration & Login | Complete | Account creation, bcrypt authentication, role-based redirect |
| Admin Dashboard | Complete | Summary statistics, recent properties, recent messages |
| Property CRUD (Admin) | Complete | Add, edit, delete with image upload and validation |
| Messages Management | Complete | View, read status toggle, delete |
| User Management | Complete | View all accounts, protected account deletion |
| Reports & Analytics | Complete | Property breakdown by type/status, price stats, top locations |
| Site Settings Panel | Complete | Site name, SMTP, pagination, notifications, maintenance mode |

### 8.2 Security Outcomes

All identified security threats within the project scope were addressed through deliberate implementation choices:

| Threat | Mitigation Implemented |
|--------|----------------------|
| SQL Injection | All queries use MySQLi prepared statements with `bind_param()` |
| Cross-Site Scripting (XSS) | All output escaped via `htmlspecialchars()` with `ENT_QUOTES` |
| Cross-Site Request Forgery (CSRF) | Cryptographic token verified on every POST request via `hash_equals()` |
| Session Fixation | `session_regenerate_id(true)` called on every successful login |
| Malicious File Upload | MIME verified via `finfo`, size limited to 5 MB, random filenames used |
| Broken Access Control | `requireLogin()` enforced on every admin page |
| Password Exposure | Passwords stored exclusively as bcrypt hashes; never logged or displayed |

### 8.3 Known Deficiencies and Future Directions

While the project satisfies its defined academic objectives, the following areas represent opportunities for enhancement in a production context:

1. **Customer-facing dashboard** with saved favorites and inquiry history — the user model supports customers but does not yet provide customer-specific authenticated features.
2. **Multi-image property gallery** — the database schema for `property_media` is designed but the gallery upload UI is not yet fully integrated with the public property detail page.
3. **Login rate limiting** — a brute-force protection mechanism (login attempt counter with lockout) is absent and should be added before production deployment.
4. **Environment variable configuration** — database credentials should be moved out of `config/db.php` and into server-level environment variables or a `.env` file for production security.
5. **Property-specific inquiry form** — visitors currently cannot submit an inquiry tied to a specific listing; a dedicated enquiry module per property is planned.
6. **Advanced sorting** — properties can be filtered but not user-sorted (e.g., by price ascending/descending or listing date).

### 8.4 Academic Assessment

The project demonstrates competency in the following academic and professional areas:

- **Relational database design** — normalized schema with appropriate data types (`DECIMAL` for monetary values, `ENUM` for constrained fields, `InnoDB` for referential integrity support).
- **Server-side web development** — PHP server-side rendering following a consistent, maintainable page architecture pattern.
- **Web application security** — implementation of OWASP-aligned defenses against the most common web vulnerabilities within the project scope.
- **Software engineering principles** — separation of concerns through shared includes, centralized configuration, and reusable helper functions.
- **User experience design** — responsive CSS layout, CSS custom properties (design tokens), hover micro-animations, and client-side form validation for UX enhancement.

The system is assessed as a solid academic-grade implementation that provides a complete, demonstrable, and technically explainable product appropriate for university-level project evaluation and defense.

---

## 9. Conclusion and Recommendation

### 9.1 Conclusion

This project set out to address a clearly identified operational gap in the Somaliland real estate sector: the absence of a centralized, accessible, and affordable digital platform for property listing management and client communication. The Guryo Samo Real Estate Management System was designed and developed in direct response to this need, delivering a fully functional, dual-sided web application that serves both administrative staff and prospective property buyers.

Over the course of the project, all seven specific objectives were met. A structured relational database was designed and implemented; a public-facing property portal with search and filter functionality was developed; a secured, role-based administrative dashboard was built with full CRUD capability; an industry-standard authentication and session management system was integrated; a contact and messaging module was deployed; multilayered input validation and security mechanisms were enforced throughout; and an administrative analytics and reporting module was delivered.

From a technical standpoint, the system demonstrates sound application of server-side web development principles, relational database design, and web application security practices. The implementation of defenses against SQL Injection, Cross-Site Scripting, Cross-Site Request Forgery, session fixation, and malicious file uploads — all using PHP's native standard library — reflects a security-conscious development approach appropriate for a production-grade web application.

From an academic standpoint, the project successfully demonstrates the developer's competency across the full web application development lifecycle: from requirements analysis and system design, through implementation and security integration, to testing, documentation, and deployment preparation. The system is complete, demonstrable, and technically explainable — meeting the expectations of a university-level final project.

### 9.2 Recommendations

Based on the outcomes of the project and the identified deficiencies documented in Section 8.3, the following recommendations are proposed for future development cycles:

**Immediate Priority (Pre-Production Deployment):**

1. **Implement login rate limiting.** A login attempt counter with a time-based account lockout should be added to the authentication endpoint to protect against automated brute-force credential attacks.

2. **Migrate credentials to environment variables.** Database connection credentials currently stored in `config/db.php` should be relocated to server-level environment variables or a `.env` file managed by a library such as `vlucas/phpdotenv`, ensuring credentials are never committed to version control or exposed through file access.

3. **Enforce HTTPS.** An `.htaccess` redirect rule should be added to force all traffic over TLS, and a free SSL certificate should be obtained via Let's Encrypt prior to public deployment.

4. **Add a password change interface.** Administrative and customer users currently have no in-application mechanism to update their passwords. A dedicated profile/password management page should be developed for all user roles.

**Short-Term Enhancements (Post-Launch, Sprint 1–2):**

5. **Develop a customer-facing dashboard.** The registered customer experience should be extended to include a personalized dashboard featuring saved property favorites, property-specific inquiry submission, and a history of past interactions.

6. **Implement a multi-image gallery per property.** The `property_media` table is already designed in the database schema; the remaining work is to complete the gallery upload interface on the add/edit property admin forms and integrate a gallery viewer on the public property detail page.

7. **Add a property-specific inquiry form.** A "Request Viewing" or "Make Enquiry" button on each property detail page — linked to that property's ID — would significantly improve the conversion path for prospective buyers and provide more structured data to administrative staff.

8. **Introduce advanced sorting options.** The properties listing page should be extended with a sort control allowing users to order results by price (ascending/descending) or listing date, improving the overall browse experience.

**Long-Term Improvements (Future Versions):**

9. **Adopt a lightweight MVC framework.** As the system grows in complexity, migrating to a structured framework such as Laravel or Slim would improve code maintainability, testability, and the separation of business logic from presentation concerns.

10. **Integrate Chart.js for visual reporting.** Replacing the current CSS-based bar representations in the admin reports module with a dedicated JavaScript charting library would significantly enhance the professionalism and interpretability of the analytics dashboard.

11. **Implement full-text search.** MySQL's `FULLTEXT` index on the `properties` table's `title`, `description`, and `location` columns would yield substantially faster and more semantically relevant search results than the current `LIKE '%keyword%'` approach, particularly as the number of listings scales.

12. **Add multi-language support.** Given the bilingual nature of the target user base (English and Somali), incorporating a language toggle with a localization file structure would meaningfully broaden the system's accessibility and adoption potential.

In summary, Guryo Samo represents a solid and well-structured foundation upon which a production-ready real estate management platform can be built. With the application of the recommended improvements — particularly those in the immediate and short-term categories — the system would be capable of supporting the day-to-day operational needs of a local real estate agency in Somaliland.

---

## Setup Instructions

### Prerequisites

Install [XAMPP](https://www.apachefriends.org) and start **Apache** and **MySQL** from the Control Panel.

### Installation Steps

1. Copy the entire `Guryo Samo` folder into `C:\xampp\htdocs\Guryo Samo\`
2. Open `http://localhost/phpmyadmin` > **Import** > select `database/real_estate.sql` > **Go**
3. Verify credentials in `config/db.php` (default XAMPP: `root` with empty password)
4. Visit the site at `http://localhost/Guryo%20Samo/index.php`
5. Log in as admin at `http://localhost/Guryo%20Samo/login.php`
   - **Username:** `admin`
   - **Password:** `Admin@123`

> Change the default admin password immediately before any public-facing deployment.

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend Language | PHP 7.4+ |
| Database | MySQL 5.7+ (InnoDB, utf8mb4) |
| Database Driver | MySQLi (Object-Oriented) |
| Web Server | Apache HTTP Server (via XAMPP) |
| Frontend | HTML5, Vanilla CSS3, Vanilla JavaScript |
| Typography | Google Fonts (Inter, Poppins) |
| Email Library | PHPMailer 6.x (SMTP) |
| Version Control | Git |

No external CSS or JavaScript frameworks (Bootstrap, React, Vue, Laravel, etc.) were used. The system is developed entirely from scratch to ensure full comprehension and explainability of every component during project presentation and defense.

---

*Guryo Samo — Real Estate Management System | Academic Project | Developed for local deployment in Somaliland*

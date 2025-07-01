Okay, here's a comprehensive `README.md` file for your CRM project, summarizing its features and providing installation instructions.

-----

# CRM Application with Advanced Contact Management

This project implements a CRM (Customer Relationship Management) system focusing on robust contact management, including dynamic custom fields, AJAX-powered interactions, and a sophisticated contact merging feature designed for data integrity.

## Table of Contents

1.  [About The Project](https://www.google.com/search?q=%23about-the-project)
      * [Key Features](https://www.google.com/search?q=%23key-features)
2.  [Demonstration Video](https://www.google.com/search?q=%23demonstration-video)
3.  [Installation Guide](https://www.google.com/search?q=%23installation-guide)
      * [Prerequisites](https://www.google.com/search?q=%23prerequisites)
      * [Steps](https://www.google.com/search?q=%23steps)
4.  [Usage](https://www.google.com/search?q=%23usage)
5.  [Database Schema Overview](https://www.google.com/search?q=%23database-schema-overview)
6.  [Technical Considerations & Best Practices](https://www.google.com/search?q=%23technical-considerations--best-practices)
7.  [Contributing](https://www.google.com/search?q=%23contributing)
8.  [License](https://www.google.com/search?q=%23license)
9.  [Contact](https://www.google.com/search?q=%23contact)

-----

## About The Project

This CRM application provides a robust solution for managing contact information, emphasizing flexibility, user experience, and data integrity. It leverages a modern PHP framework (Laravel) to deliver a responsive and feature-rich interface.

### Key Features

  * **CRUD Operations:** Full Create, Read, Update, and Delete functionality for contacts.
  * **Standard Contact Fields:**
      * Name
      * Email
      * Phone
      * Gender (Radio button)
      * Profile Image (File upload)
      * Additional File (Document upload)
  * **Dynamic Custom Fields:**
      * Administrators can define and manage additional custom fields (e.g., "Birthday," "Company Name," "Address," "Favorite Color," "Membership Status").
      * Supports various field types (text, dropdowns, etc.).
      * The UI dynamically renders these custom fields on contact creation/editing forms.
      * Custom field values are stored in an extensible database schema, ensuring scalability.
  * **AJAX Integration:**
      * Seamless Insert, Update, and Delete operations for contacts without full page refreshes.
      * Real-time success and error messages.
  * **Filtering and Search:**
      * Dynamic filtering by Name, Email, and Gender.
      * All filtering is performed via AJAX for instant results.
      * (Optional: Can be extended to filter by custom fields).
  * **Advanced Contact Merging:**
      * Dedicated feature to merge two contact records.
      * **Master Contact Selection:** Users select a "master" contact (the primary record) from a modal.
      * **Confirmation & Policy:** A final confirmation screen outlines the merge policy:
          * Master contact's core data is retained.
          * Unique emails and phone numbers from the secondary contact are added to the master.
          * Custom fields are merged: If the master lacks a secondary's custom field value, it's added. If both have the same custom field but different values (e.g., 'Address'), values are combined (e.g., comma-separated).
      * **Data Integrity:** The secondary contact is not deleted but marked as `is_merged` and `merged_into_id` is set to the master's ID, preserving its history and ensuring no data loss.
      * The UI clearly indicates merged fields/values on the master contact's detail page.

-----

## Demonstration Video

A short video demonstrating all the functionalities and database changes during the merge process can be found here:

**[Link to Your Video Here]**
*(Remember to record and upload your video, then replace this placeholder with the actual link.)*

-----

## Installation Guide

To get this project up and running on your local machine, follow these steps.

### Prerequisites

Before you begin, ensure you have the following installed:

  * **PHP:** Version 8.1 or higher (Laravel 10 typically requires PHP 8.1+).
  * **Composer:** For PHP dependency management. ([Get Composer](https://getcomposer.org/download/))
  * **Node.js & npm:** For frontend asset compilation. ([Download Node.js](https://nodejs.org/en/download/))
  * **Database:** MySQL, PostgreSQL, or SQLite. (MySQL is commonly used with Laravel).
  * **Git:** For cloning the repository.

### Steps

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/your-crm-repo.git
    cd your-crm-repo
    ```

2.  **Install PHP dependencies:**

    ```bash
    composer install
    ```

3.  **Set up environment variables:**

      * Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
      * Open the newly created `.env` file and configure your database connection:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=your_crm_db
        DB_USERNAME=your_db_user
        DB_PASSWORD=your_db_password
        ```
        *(Replace `your_crm_db`, `your_db_user`, `your_db_password` with your actual database credentials.)*

4.  **Generate application key:**

    ```bash
    php artisan key:generate
    ```

5.  **Run database migrations:**
    This will create all the necessary tables in your database.

    ```bash
    php artisan migrate
    ```

6.  **Seed the database (Optional but Recommended):**
    If you have seeders for initial data (e.g., sample contacts or custom field definitions), run them:

    ```bash
    php artisan db:seed
    ```

7.  **Install Node.js dependencies and compile frontend assets:**

    ```bash
    npm install
    npm run dev  # Or npm run watch for continuous compilation during development
    ```

8.  **Start the Laravel development server:**

    ```bash
    php artisan serve
    ```

Your application should now be accessible in your web browser at `http://127.0.0.1:8000` (or whatever address `php artisan serve` provides).

-----

## Usage

  * Navigate to the root URL (`http://127.0.0.1:8000`).
  * You will see the contact list.
  * Use the "Add Contact" button to create new contacts.
  * Edit/Delete options are available for each contact.
  * Utilize the search/filter fields to dynamically update the contact list.
  * To merge contacts, select two contacts from the list (using checkboxes or a dedicated merge button/link), and follow the on-screen prompts to choose the master and confirm the merge.
  * (If implemented) Access the Custom Fields management section (e.g., `/admin/custom-fields`) to add new dynamic fields.

-----

## Database Schema Overview

The core functionality relies on a well-structured database schema:

  * **`contacts` table:**
      * `id` (PK)
      * `name`
      * `email` (main email)
      * `phone` (main phone)
      * `gender`
      * `profile_image`
      * `additional_file`
      * `is_merged` (BOOLEAN, default `false`): Flags if this contact has been merged into another.
      * `merged_into_id` (BIGINT UNSIGNED, nullable, FK to `contacts.id`): References the `id` of the master contact this record was merged into.
      * Standard Laravel timestamps (`created_at`, `updated_at`).
  * **`contact_emails` table:**
      * `id` (PK)
      * `contact_id` (FK to `contacts.id`)
      * `email`
      * `type` (e.g., 'work', 'personal', 'alternate')
      * `is_primary` (BOOLEAN)
  * **`contact_phones` table:**
      * `id` (PK)
      * `contact_id` (FK to `contacts.id`)
      * `phone`
      * `type` (e.g., 'mobile', 'home', 'work', 'alternate')
      * `is_primary` (BOOLEAN)
  * **`custom_fields` table:**
      * `id` (PK)
      * `name` (e.g., 'Birthday', 'Company Role')
      * `slug` (unique identifier, e.g., 'birthday', 'company\_role')
      * `type` (e.g., 'text', 'textarea', 'number', 'date', 'dropdown')
      * `options` (JSON for dropdown choices, nullable)
  * **`contact_custom_fields` table:**
      * `id` (PK)
      * `contact_id` (FK to `contacts.id`)
      * `custom_field_id` (FK to `custom_fields.id`)
      * `value` (TEXT or VARCHAR, stores the actual value for the custom field)

-----

## Technical Considerations & Best Practices

  * **Extensible Custom Fields:** The separate `custom_fields` and `contact_custom_fields` tables ensure that adding new custom fields does not require database schema changes on the `contacts` table, allowing for dynamic extensibility.
  * **AJAX for Responsiveness:** Extensive use of AJAX provides a modern, responsive user experience by updating content without full page reloads for common operations.
  * **Data Integrity in Merging:** The merge logic prioritizes data retention. By marking secondary contacts as merged (`is_merged` flag) rather than deleting them, historical data and audit trails are preserved. Related entities (emails, phones, custom fields) are reassigned or combined under the master contact.
  * **Modular Design:** The codebase follows a clear MVC (Model-View-Controller) pattern with service classes (`ContactMergeService`) for complex business logic, promoting maintainability and reusability.

-----

## Contributing

Contributions are welcome\! If you find a bug or have an idea for an enhancement, please open an issue or submit a pull request.

-----

## License

This project is open-source and available under the [MIT License](https://opensource.org/licenses/MIT).

-----

## Contact

For any inquiries or feedback, please reach out to:
[vikasmrnv@gmail.com]


-----

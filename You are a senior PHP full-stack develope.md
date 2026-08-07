You are a senior PHP full-stack developer and software architect.

I am building a capstone project called "Idle Land for Community Gardening System."

## Tech Stack
- PHP 8
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Leaflet.js + OpenStreetMap
- Apache (XAMPP)

## IMPORTANT

I have attached a file named `design.md`.

Use this file as the PRIMARY reference for:
- UI design
- Page layouts
- Navigation
- Components
- Colors
- User flow
- Dashboard layouts
- Forms
- Tables
- Buttons
- Icons
- Overall design system

Do NOT redesign the interface unless the design.md is missing something important.

If a feature is not described in design.md, keep the style consistent with the rest of the design.

## Development Rules

- Follow the design.md exactly.
- Generate clean, maintainable PHP code.
- Separate HTML, CSS, JavaScript, and PHP whenever possible.
- Use reusable components such as header.php, sidebar.php, footer.php, navbar.php, and database.php.
- Use prepared statements (PDO or MySQLi).
- Validate all user input.
- Prevent SQL Injection and XSS.
- Write modular code.
- Comment important sections.
- Make the UI responsive using Bootstrap 5.

## Project Modules

Administrator
- Manage users
- Manage lands
- View reports
- Dashboard

Landowner
- Register land
- Upload images
- Manage lands
- View requests
- Approve/Reject requests
- View schedules

Gardener
- Browse available lands
- View map
- Request plots
- View schedules
- Record harvests

## Database

Before generating any PHP pages:

1. Design the complete MySQL database.
2. Explain the relationships.
3. Create SQL scripts.

## Development Process

Build the project ONE MODULE AT A TIME.

Never generate the entire project at once.

For every module:

1. Explain the files that will be created.
2. Generate the SQL if needed.
3. Generate the PHP files.
4. Explain how the files connect.
5. Wait for my confirmation before continuing.

If design.md conflicts with my instructions, always follow design.md.
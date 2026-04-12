# ProjekKu v2

ProjekKu v2 is a web-based business management system rebuilt from the ground up as
a PHP + MySQL application, replacing the original Python/Tkinter desktop app (v1).
It is designed to help small businesses — particularly service or installation
contractors — manage their entire operational workflow from a single browser
interface, accessible on any device running a local XAMPP server.

The system covers the full data lifecycle of a project-based business:

- **Customer & Supplier Management** — store contact details, addresses, and
  supplier status (Active/Inactive) for all external parties
- **Product & Material Cataloguing** — define product types, link raw materials
  to each product, and track cost-per-unit sourced from specific suppliers
- **Job Roles & Labour Costs** — register job types and their daily wage rates,
  used to calculate installation expenses per project
- **Project Recording** — log projects with dates, assigned products, quantities,
  and the customer being served, with full labour allocation per project
- **Insight & Profitability Reporting** — view per-project gross profit calculated
  live from material costs, labour costs, and project value — no stored derived data

Unlike v1, which ran as a single Python file on Windows, v2 runs entirely in a
browser and requires no Python environment. All logic is handled in procedural PHP,
all data is persisted in a relational MySQL database via `setup.sql`, and all pages
are protected by session-based authentication — unauthenticated users are
automatically redirected to the login screen.
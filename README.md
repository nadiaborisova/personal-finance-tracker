# Personal Finance Tracker (with REST API)

A robust and modern financial management system built with **Laravel 12** and **Filament PHP v3**. This application allows users to track their income and expenses through a beautiful web interface and offers a powerful **REST API** for external integrations.

## Key Features
* **Real-Time Financial Overview** – Instantly monitor your Total Balance (current month net result), Monthly Income, and Monthly Expenses, calculated dynamically based on the active month and year.
* **6-Month Income vs. Expense Trends** – Visual comparison of Income and Expenses over the last six months using smooth line charts. This allows you to track financial momentum, identify seasonal patterns, and evaluate overall financial stability.
* **Daily Expense Tracking (Current Month)** – A full-width daily line chart displaying total expenses per day for the current month, helping you detect spending spikes and analyze short-term cash flow behavior.
* **Expense Distribution by Category** – A doughnut chart that aggregates total expenses per category, giving you a clear breakdown of where your money is being spent and highlighting dominant cost areas.
* **Smart Budgeting** – Set limits by category with automatic tracking of spent amounts and percentage progress.
* **Category Management** – Full control over transaction types with custom color-coded categories.
* **Transaction Management** – Full CRUD system with categorized records and custom transaction dates.
* **RESTful API Ready** – Fully grouped endpoints for Transactions, Categories, and Budgets, secured by Laravel Sanctum.
* **Recurring Transactions** – Automate repeating income and expenses (e.g. rent, salary) with configurable frequency (daily, weekly, monthly, yearly). Transactions are generated automatically via Laravel Scheduler, with smart duplicate detection that prevents re-creation if a matching transaction already exists for the period.
* **Automated Testing** – Comprehensive PHPUnit test suite ensuring 100% accuracy in balance calculations and data integrity.
* **Sleek UI/UX** – A responsive, dark-mode-ready interface powered by Filament PHP and Tailwind CSS, featuring an organized widget-based layout.

## Tech Stack
- **Framework:** Laravel 12
- **Admin Panel:** Filament PHP v3
- **API Architecture:** RESTful API (Laravel Sanctum)
- **Data Visualization:** Laravel Trend / Chart.js
- **Task Scheduling:** Laravel Scheduler (Recurring Transactions)
- **Database:** SQLite (Default) / MySQL
- **Testing:** PHPUnit

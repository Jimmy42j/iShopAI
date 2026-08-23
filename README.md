# iShopAI — AI-Powered Clothing E-Commerce Platform

> An intelligent full-stack clothing e-commerce platform with personalized AI product recommendations and an AI shopping assistant.

**iShopAI** is a full-stack clothing e-commerce platform designed to provide a modern online shopping experience enhanced by artificial intelligence. The system combines a React-based frontend, a PHP REST API backend, a MySQL relational database, and a dedicated Python/Flask AI recommendation microservice.

The platform supports core e-commerce functionality such as authentication, product browsing, categories, shopping carts, wishlists, checkout, orders, and personalized recommendations. The AI service analyzes user behavior and contextual information to generate relevant product recommendations and conversational shopping assistance.

> **Project Type:** Full-Stack E-Commerce + AI
> **Status:** Educational / Portfolio Project

---

## ✨ Features

### 🛍️ E-Commerce

* Browse products
* Product categories
* Product search
* Product details
* Related products
* Product variants
* Shopping cart
* Wishlist
* Checkout
* Order management
* Product images
* Customer addresses

### 🔐 Authentication & Security

* User registration
* User login
* JWT-based authentication
* Access-controlled routes
* Token refresh
* Logout
* CORS configuration
* Request validation
* Environment-based configuration

### 🤖 AI-Powered Recommendations

The platform includes a dedicated AI recommendation microservice that can generate personalized product recommendations using:

* User viewing history
* Wishlist activity
* Cart activity
* Gender context
* Seasonal context
* Product scoring and ranking
* Personalized recommendation explanations

The recommendation system can also use a collaborative-filtering model when available and falls back to a recommendation engine when the ML model is unavailable.

### 💬 AI Shopping Assistant

The AI service also provides a conversational endpoint for shopping assistance.

The assistant can:

* Understand recommendation-related requests
* Detect user intent
* Extract relevant entities
* Identify seasonal requests
* Generate product recommendations
* Provide explanations for recommendations
* Respond to general clothing-related queries

### 🧠 Recommendation Intelligence

The recommendation service contains components for:

* Natural Language Understanding
* Intent detection
* Entity extraction
* Product filtering
* Product scoring
* Recommendation ranking
* Recommendation explanations
* Collaborative filtering
* Fallback recommendations

---

## 🏗️ Architecture

The application follows a modular multi-service architecture.

```text
                         ┌─────────────────────┐
                         │       User          │
                         └──────────┬──────────┘
                                    │
                                    ▼
                    ┌────────────────────────────┐
                    │ React + TypeScript Frontend│
                    │       Vite Application     │
                    └─────────────┬──────────────┘
                                  │
                         HTTP / REST API
                                  │
                                  ▼
                    ┌────────────────────────────┐
                    │       PHP Backend           │
                    │     Slim Framework 4        │
                    │                             │
                    │ Authentication             │
                    │ Products                   │
                    │ Categories                 │
                    │ Cart                       │
                    │ Wishlist                   │
                    │ Orders                     │
                    │ Recommendations            │
                    └──────────┬──────────┬───────┘
                               │          │
                               │          │ HTTP
                               │          ▼
                               │  ┌─────────────────────┐
                               │  │ Python / Flask AI   │
                               │  │ Recommendation      │
                               │  │ Microservice        │
                               │  └─────────────────────┘
                               │
                               ▼
                    ┌────────────────────────────┐
                    │       MySQL 8 Database     │
                    │                            │
                    │ Users                      │
                    │ Products                   │
                    │ Categories                 │
                    │ Carts                      │
                    │ Wishlists                  │
                    │ Orders                     │
                    │ Recommendations            │
                    └────────────────────────────┘
```

---

## 🧰 Technology Stack

### Frontend

| Technology      | Purpose                       |
| --------------- | ----------------------------- |
| React 18        | UI development                |
| TypeScript      | Type-safe development         |
| Vite            | Development and build tooling |
| React Router    | Client-side routing           |
| Redux Toolkit   | Application state management  |
| React Redux     | Redux integration             |
| TanStack Query  | Server-state management       |
| Axios           | HTTP communication            |
| React Hook Form | Form management               |
| Zod             | Validation                    |
| Tailwind CSS    | Styling                       |
| Framer Motion   | UI animations                 |
| Lucide React    | Icons                         |
| React Hot Toast | Notifications                 |
| Vitest          | Testing                       |

### Backend

| Technology         | Purpose                       |
| ------------------ | ----------------------------- |
| PHP 8.2+           | Backend language              |
| Slim Framework 4   | REST API framework            |
| PHP-DI             | Dependency injection          |
| MySQL 8            | Relational database           |
| Firebase PHP-JWT   | JWT authentication            |
| Respect/Validation | Request validation            |
| PHP dotenv         | Environment configuration     |
| Monolog            | Application logging           |
| CORS Middleware    | Cross-origin request handling |

### AI Service

| Technology              | Purpose                          |
| ----------------------- | -------------------------------- |
| Python                  | AI service development           |
| Flask                   | AI REST API                      |
| Collaborative Filtering | Recommendation model             |
| NLU Engine              | Natural language understanding   |
| Product Scoring         | Recommendation ranking           |
| Recommendation Engine   | Personalized product suggestions |

### DevOps

| Technology         | Purpose                            |
| ------------------ | ---------------------------------- |
| Docker             | Containerization                   |
| Docker Compose     | Multi-service orchestration        |
| Nginx              | Reverse proxy / production routing |
| MySQL Docker Image | Database container                 |

---

## 📁 Project Structure

```text
CRP_Project/
│
├── ai-service/
│   ├── models/
│   │   ├── simple_cf.py
│   │   └── __init__.py
│   │
│   ├── app.py
│   ├── train_simple_model.py
│   ├── test_chat.py
│   ├── requirements.txt
│   ├── README.md
│   ├── ML_TRAINING_GUIDE.md
│   └── QUICK_START_ML.md
│
├── backend/
│   ├── config/
│   │   ├── container.php
│   │   └── routes.php
│   │
│   ├── public/
│   │   └── index.php
│   │
│   ├── src/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── CartController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ProductController.php
│   │   │   ├── RecommendationController.php
│   │   │   └── WishlistController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── CorsMiddleware.php
│   │   │   └── JwtMiddleware.php
│   │   │
│   │   └── Services/
│   │       ├── AuthService.php
│   │       ├── DatabaseService.php
│   │       └── RecommendationService.php
│   │
│   ├── composer.json
│   └── env.example
│
├── database/
│   ├── schema.sql
│   ├── seeders.sql
│   ├── setup.sql
│   └── database update scripts
│
├── docker/
│   ├── docker-compose.yml
│   ├── Dockerfile.ai-service
│   ├── Dockerfile.backend
│   ├── Dockerfile.frontend
│   └── nginx-frontend.conf
│
├── frontend/
│   ├── src/
│   ├── public/
│   ├── package.json
│   ├── tsconfig.json
│   ├── vite.config.ts
│   └── tailwind.config.js
│
└── README.md
```

---

# 🔌 Backend API

The backend exposes a RESTful API under:

```text
/api
```

## Health Check

```http
GET /api/health
```

Returns the current API status and version information.

---

## Authentication

```http
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
GET  /api/auth/me
POST /api/auth/refresh
```

JWT middleware protects authenticated endpoints.

---

## Categories

```http
GET /api/categories
GET /api/categories/{slug}
```

---

## Products

```http
GET /api/products
GET /api/products/{slug}
GET /api/products/{id}/related
GET /api/products/search
```

---

## Shopping Cart

```http
GET    /api/cart
POST   /api/cart
PATCH  /api/cart/{itemId}
DELETE /api/cart/{itemId}
DELETE /api/cart
```

---

## Wishlist

```http
GET    /api/wishlist
POST   /api/wishlist
DELETE /api/wishlist/{productId}
```

Wishlist operations require authentication.

---

## Orders

```http
GET  /api/orders
GET  /api/orders/{id}
POST /api/orders/checkout
```

Order operations require authentication.

---

## AI Recommendations

```http
POST /api/ai/recommend
GET  /api/ai/explain/{productId}
```

The backend communicates with the AI microservice to retrieve personalized product recommendations.

---

# 🤖 AI Recommendation Service

The AI service runs independently from the main PHP backend.

Default service URL:

```text
http://localhost:5000
```

## Health Check

```http
GET /health
```

## Recommendations

```http
POST /recommend
```

Example request:

```json
{
  "gender_context": "men",
  "season": "summer",
  "user_signals": {
    "viewed": [1, 2, 3],
    "wishlisted": [4, 5],
    "carted": [6]
  },
  "topk": 8
}
```

## Product Explanation

```http
GET /explain/{product_id}
```

## AI Chat

```http
POST /chat
```

The chat endpoint provides conversational assistance and can generate personalized clothing recommendations.

## Model Information

```http
GET /model/info
```

---

# 🗄️ Database

The application uses **MySQL 8** as its primary relational database.

The database schema includes entities for:

```text
users
categories
products
product_images
variants
carts
cart_items
wishlists
wishlist_items
addresses
orders
order_items
sessions
recommendation_logs
```

The database resources are located inside:

```text
database/
```

Important files include:

```text
schema.sql
seeders.sql
setup.sql
```

---

# 🚀 Getting Started

## Prerequisites

Make sure the following tools are installed:

* Node.js
* npm
* PHP 8.2+
* Composer
* MySQL 8+
* Python 3
* pip
* Docker and Docker Compose (optional but recommended)

---

## Option 1 — Run with Docker

Docker Compose is provided for running the main services together.

From the project directory:

```bash
cd docker
docker compose up --build
```

The Docker configuration provides containers for:

```text
MySQL
PHP Backend
AI Service
Frontend
Nginx
```

### Services

| Service     | Default Port |
| ----------- | -----------: |
| Frontend    |           80 |
| Backend API |         8000 |
| AI Service  |         5000 |
| MySQL       |         3306 |
| Nginx HTTPS |          443 |

---

# 🖥️ Manual Development Setup

## 1. Clone the Repository

```bash
git clone https://github.com/<your-username>/ishop-ai.git
cd ishop-ai
```

---

## 2. Configure the Database

Create the MySQL database:

```sql
CREATE DATABASE clothing_ecommerce;
```

Import the database schema:

```bash
mysql -u root -p clothing_ecommerce < database/schema.sql
```

Import the seed data:

```bash
mysql -u root -p clothing_ecommerce < database/seeders.sql
```

---

## 3. Configure the Backend

Move into the backend directory:

```bash
cd backend
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp env.example .env
```

Update the database and JWT configuration inside `.env`.

Example:

```env
APP_ENV=development
APP_DEBUG=true

DB_HOST=localhost
DB_PORT=3306
DB_NAME=clothing_ecommerce
DB_USER=root
DB_PASS=your_database_password

JWT_SECRET=change-this-secret
JWT_ALGORITHM=HS256
JWT_EXPIRY=3600

AI_SERVICE_URL=http://localhost:5000
```

Start the backend:

```bash
composer serve
```

The API will be available at:

```text
http://localhost:8000
```

---

# 🤖 4. Start the AI Service

Move into the AI service:

```bash
cd ai-service
```

Create a Python virtual environment:

```bash
python -m venv .venv
```

Activate it on Windows:

```bash
.venv\Scripts\activate
```

Install dependencies:

```bash
pip install -r requirements.txt
```

Start the service:

```bash
python app.py
```

The AI service will be available at:

```text
http://localhost:5000
```

---

# 🎨 5. Start the Frontend

Move into the frontend directory:

```bash
cd frontend
```

Install dependencies:

```bash
npm install
```

Start the development server:

```bash
npm run dev
```

Vite will provide the frontend development URL in the terminal.

---

# 🧪 Testing

The frontend uses **Vitest** for testing.

Run the test suite:

```bash
npm test
```

Run the test suite with coverage:

```bash
npm run test:coverage
```

The PHP backend also provides development scripts for testing and code quality:

```bash
composer test
composer test-coverage
composer phpstan
composer cs-check
```

---

# 🔒 Security

The project includes several security-related mechanisms:

* JWT authentication
* Protected authenticated routes
* Password authentication through the backend
* Request validation
* CORS configuration
* Environment-based secrets
* HTTP-only/session security configuration options
* Rate-limiting configuration
* File upload restrictions
* Separation of AI and core application services

### Production Security

Before production deployment:

* Replace all default secrets.
* Never commit `.env` files.
* Use HTTPS.
* Use strong JWT secrets.
* Configure production database credentials.
* Restrict CORS origins.
* Disable development/debug settings.
* Use secure cookies where applicable.
* Configure proper database permissions.
* Add centralized logging and monitoring.

---

# 📦 Deployment Architecture

A production deployment can be organized using Docker and Nginx:

```text
                         Internet
                            │
                            ▼
                     ┌─────────────┐
                     │    Nginx    │
                     │ Reverse Proxy│
                     └──────┬──────┘
                            │
             ┌──────────────┴──────────────┐
             ▼                             ▼
      ┌─────────────┐              ┌─────────────┐
      │  Frontend   │              │   Backend   │
      │ React/Vite  │              │ PHP/Slim    │
      └─────────────┘              └──────┬──────┘
                                          │
                         ┌────────────────┼───────────────┐
                         ▼                ▼               ▼
                    ┌─────────┐    ┌────────────┐   ┌──────────┐
                    │ MySQL   │    │ AI Service │   │ Storage  │
                    │   DB    │    │ Flask      │   │ Uploads  │
                    └─────────┘    └────────────┘   └──────────┘
```

---

# 🧩 Design Principles

The project follows several software engineering principles:

### Separation of Concerns

Frontend, backend, database, and AI functionality are separated into independent components.

### Modular Architecture

Business functionality is divided into controllers, services, middleware, and dedicated microservices.

### API-Driven Communication

The frontend communicates with the PHP backend through REST APIs.

### Microservice-Based AI

AI functionality is isolated into a dedicated Flask service rather than being tightly coupled to the main backend.

### Scalable Architecture

The independent AI service can be scaled separately from the main application.

---

# 📈 Future Improvements

Potential improvements for future versions include:

* Real production-grade recommendation model
* More advanced collaborative filtering
* Deep-learning recommendation models
* Improved natural language understanding
* Product image similarity search
* AI-powered visual search
* Personalized home-page recommendations
* Real-time inventory management
* Payment gateway integration
* Email notifications
* Admin dashboard
* Advanced analytics
* Automated CI/CD
* Cloud deployment
* Redis caching
* Message queues
* Centralized logging
* Monitoring and observability
* Automated security testing

---

# 🎯 Project Goals

The main goals of iShopAI are to demonstrate the development of a modern full-stack e-commerce platform while integrating artificial intelligence into the shopping experience.

The project demonstrates practical knowledge of:

* Full-stack web development
* REST API development
* React and TypeScript
* PHP backend development
* Relational database design
* JWT authentication
* State management
* Server-state management
* Microservice architecture
* AI recommendation systems
* Natural language processing concepts
* Docker containerization
* Automated testing
* Software security

---

# 📊 Project Highlights

| Area             | Implementation                    |
| ---------------- | --------------------------------- |
| Frontend         | React + TypeScript + Vite         |
| Backend          | PHP 8.2 + Slim Framework 4        |
| Database         | MySQL 8                           |
| Authentication   | JWT                               |
| State Management | Redux Toolkit                     |
| Server State     | TanStack Query                    |
| HTTP Client      | Axios                             |
| Validation       | Zod + Respect Validation          |
| AI Service       | Python + Flask                    |
| Recommendation   | Collaborative Filtering + Scoring |
| AI Assistant     | NLU-based Chat                    |
| Containerization | Docker                            |
| Reverse Proxy    | Nginx                             |
| Testing          | Vitest + PHPUnit/Pest             |
| Code Quality     | PHPStan + PHP_CodeSniffer         |

---

# 📜 Disclaimer

**iShopAI is an independent educational and portfolio project.**

The project is designed as a clothing e-commerce demonstration and is **not affiliated with, endorsed by, or sponsored by Apple Inc.**

Any third-party product names, brands, images, or trademarks used within sample data remain the property of their respective owners.

---

# 👨‍💻 Author

**Kyaw Moe Htut**

GitHub:

```text
https://github.com/Jimmy42j
```

---

# 📄 License

This project is licensed under the **MIT License** unless otherwise specified.

See the `LICENSE` file for more information.

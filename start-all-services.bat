@echo off
echo Starting Clothing E-commerce Services...
echo.

echo [1/3] Starting Backend API (PHP)...
start "Backend API" /D "backend\public" php -S localhost:8000

echo [2/3] Starting AI Service (Python)...
start "AI Service" /D "ai-service" python app.py

echo [3/3] Starting Frontend (React)...
start "Frontend" /D "frontend" npm run dev

echo.
echo All services are starting...
echo - Backend API: http://localhost:8000
echo - AI Service: http://localhost:5000
echo - Frontend: http://localhost:5174
echo.
echo Keep all terminal windows open while using the application.
echo Press any key to exit...
pause >nul

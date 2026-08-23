# AI Recommendation Service

A Flask-based microservice that provides AI-powered product recommendations for the clothing e-commerce platform.

## Features

- **Personalized Recommendations**: Generate product suggestions based on user preferences, gender, and season
- **Contextual Filtering**: Filter products by gender target and seasonal appropriateness
- **User Behavior Signals**: Consider user's viewing, wishlist, and cart history
- **Explanation Generation**: Provide natural language explanations for recommendations
- **Mock NLU Model**: Simulates a real NLU model with scoring and ranking algorithms

## API Endpoints

### Health Check
```
GET /health
```

### Get Recommendations
```
POST /recommend
Content-Type: application/json

{
  "gender_context": "men|women|kids|unisex|detect",
  "season": "spring|summer|autumn|winter|all|auto", 
  "user_signals": {
    "viewed": [1, 2, 3],
    "wishlisted": [4, 5],
    "carted": [6]
  },
  "topk": 8
}
```

### Explain Product
```
GET /explain/<product_id>
```

### Model Information
```
GET /model/info
```

## Setup

1. Install dependencies:
```bash
pip install -r requirements.txt
```

2. Run the service:
```bash
python app.py
```

The service will start on `http://localhost:5000` by default.

## Environment Variables

- `PORT`: Port to run the service on (default: 5000)
- `DEBUG`: Enable debug mode (default: false)

## Production Deployment

For production, use gunicorn:

```bash
gunicorn -w 4 -b 0.0.0.0:5000 app:app
```

## Integration with Main Application

The main PHP backend calls this service via HTTP requests to get AI-powered recommendations. The service is designed to be stateless and can be scaled horizontally.

## Future Enhancements

- Replace mock model with actual trained NLU model
- Add caching layer for frequently requested recommendations
- Implement A/B testing for different recommendation algorithms
- Add metrics and monitoring
- Support for more complex user preferences and constraints

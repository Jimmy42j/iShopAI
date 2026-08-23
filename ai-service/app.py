#!/usr/bin/env python3
"""
AI Recommendation Service
A Flask-based microservice that provides AI-powered product recommendations
for the clothing e-commerce platform.
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import json
import random
import time
import re
import os
import requests
from typing import Dict, List, Any, Tuple
import logging

# Configure logging first
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Import ML model
try:
    from models.simple_cf import SimpleCollaborativeFiltering
    ML_AVAILABLE = True
    logger.info("ML models imported successfully")
except ImportError as e:
    ML_AVAILABLE = False
    logger.warning(f"ML models not available - using mock recommendations only: {str(e)}")

app = Flask(__name__)
CORS(app)

# Backend API configuration
BACKEND_API_URL = 'http://localhost:8000'

def fetch_real_products():
    """Fetch real products from the backend API"""
    try:
        response = requests.get(f'{BACKEND_API_URL}/products?limit=50')
        if response.status_code == 200:
            data = response.json()
            if data.get('success') and data.get('data', {}).get('items'):
                products = []
                for item in data['data']['items']:
                    product = {
                        "id": item['id'],
                        "name": item['name'],
                        "category": item['gender_target'],
                        "season": item['season'],
                        "price": float(item['price']),
                        "rating": float(item['rating_avg']) if item['rating_avg'] else 4.0,
                        "material": item.get('material', ''),
                        "brand": item.get('brand', ''),
                        "description": item.get('description', '')
                    }
                    products.append(product)
                return products
    except Exception as e:
        logger.error(f"Error fetching products from backend: {str(e)}")
    
    # Fallback to mock data if API fails
    return MOCK_PRODUCTS

# Mock product database (in production, this would connect to the main database)
MOCK_PRODUCTS = [
    {"id": 1, "name": "Classic Cotton T-Shirt", "category": "men", "season": "summer", "price": 24.99, "rating": 4.5},
    {"id": 2, "name": "Linen Button-Up Shirt", "category": "men", "season": "summer", "price": 59.99, "rating": 4.3},
    {"id": 3, "name": "Cargo Shorts", "category": "men", "season": "summer", "price": 39.99, "rating": 4.2},
    {"id": 11, "name": "Floral Summer Dress", "category": "women", "season": "summer", "price": 69.99, "rating": 4.6},
    {"id": 12, "name": "Silk Blouse", "category": "women", "season": "spring", "price": 89.99, "rating": 4.5},
    {"id": 13, "name": "High-Waisted Jeans", "category": "women", "season": "all", "price": 79.99, "rating": 4.7},
    {"id": 21, "name": "Dinosaur T-Shirt", "category": "kids", "season": "summer", "price": 19.99, "rating": 4.6},
    {"id": 22, "name": "Rainbow Dress", "category": "kids", "season": "spring", "price": 39.99, "rating": 4.5},
    {"id": 6, "name": "Wool Sweater", "category": "men", "season": "winter", "price": 89.99, "rating": 4.7},
    {"id": 16, "name": "Cashmere Cardigan", "category": "women", "season": "autumn", "price": 129.99, "rating": 4.8},
]

# Try to get real products, fallback to mock
try:
    PRODUCTS = fetch_real_products()
    logger.info(f"Loaded {len(PRODUCTS)} products from backend API")
except:
    PRODUCTS = MOCK_PRODUCTS
    logger.info("Using mock products as fallback")

class NLUEngine:
    """Natural Language Understanding engine for chatbot"""
    
    def __init__(self):
        self.intents = {
            'greeting': {
                'patterns': [r'hi', r'hello', r'hey', r'good morning', r'good afternoon', r'good evening'],
                'responses': [
                    "Hello! I'm your personal fashion assistant. How can I help you find the perfect outfit today?",
                    "Hi there! I'm here to help you discover amazing clothing. What are you looking for?",
                    "Hey! Ready to find some great fashion? Tell me what you need!"
                ]
            },
            'recommendation_request': {
                'patterns': [
                    r'recommend', r'suggest', r'show me', r'find me', r'looking for', r'need', r'want',
                    r'what should i wear', r'help me find', r'shopping for', r'i want', r'get me',
                    r'clothing', r'clothes', r'fashion', r'outfit', r'wear', r'buy', r'purchase'
                ],
                'responses': [
                    "I'd love to help you find the perfect items! Let me get some personalized recommendations for you.",
                    "Great! I'll find some amazing products that match your style and needs.",
                    "Perfect! Let me search for the best options for you."
                ]
            },
            'seasonal_query': {
                'patterns': [r'summer', r'winter', r'spring', r'autumn', r'fall', r'cold', r'hot', r'warm', r'cool'],
                'responses': [
                    "I'll find seasonal items perfect for the weather!",
                    "Great! I'll recommend weather-appropriate clothing for you."
                ]
            },
            'price_query': {
                'patterns': [r'cheap', r'expensive', r'budget', r'affordable', r'under', r'less than', r'price'],
                'responses': [
                    "I'll help you find items within your budget!",
                    "Let me show you some great value options!"
                ]
            },
            'goodbye': {
                'patterns': [r'bye', r'goodbye', r'see you', r'thanks', r'thank you'],
                'responses': [
                    "You're welcome! Happy shopping! 🛍️",
                    "Glad I could help! Enjoy your new clothes!",
                    "Thanks for chatting! Come back anytime for more recommendations!"
                ]
            }
        }
        
        # Entity extraction patterns
        self.entities = {
            'gender': {
                'men': [r'men', r'male', r'guy', r'man', r'masculine', r'him', r'his'],
                'women': [r'women', r'female', r'girl', r'woman', r'feminine', r'her', r'she'],
                'kids': [r'kids', r'children', r'child', r'boy', r'girl', r'toddler', r'baby']
            },
            'season': {
                'summer': [r'summer', r'hot', r'warm', r'beach', r'vacation'],
                'winter': [r'winter', r'cold', r'snow', r'freezing', r'warm clothes'],
                'spring': [r'spring', r'mild', r'fresh'],
                'autumn': [r'autumn', r'fall', r'cool']
            },
            'clothing_type': {
                'shirt': [r'shirt', r'top', r'blouse', r't-shirt', r'tee'],
                'pants': [r'pants', r'jeans', r'trousers', r'bottoms'],
                'dress': [r'dress', r'gown', r'frock'],
                'jacket': [r'jacket', r'coat', r'blazer', r'cardigan'],
                'shoes': [r'shoes', r'boots', r'sneakers', r'sandals', r'footwear']
            },
            'price_range': {
                'budget': [r'cheap', r'affordable', r'budget', r'under 30', r'less than 30'],
                'mid': [r'mid-range', r'moderate', r'30-80', r'reasonable'],
                'premium': [r'expensive', r'luxury', r'premium', r'over 80', r'high-end']
            }
        }
    
    def understand_message(self, message: str) -> Dict[str, Any]:
        """Analyze user message and extract intent and entities"""
        message_lower = message.lower()
        
        # Detect intent
        intent = self._detect_intent(message_lower)
        
        # Extract entities
        entities = self._extract_entities(message_lower)
        
        # Generate response
        response = self._generate_response(intent, entities)
        
        return {
            'intent': intent,
            'entities': entities,
            'response': response,
            'confidence': self._calculate_confidence(intent, entities)
        }
    
    def _detect_intent(self, message: str) -> str:
        """Detect the primary intent from the message"""
        # Check for recommendation patterns first (higher priority)
        for pattern in self.intents['recommendation_request']['patterns']:
            if re.search(pattern, message):
                return 'recommendation_request'
        
        # Then check other intents
        for intent, data in self.intents.items():
            if intent == 'recommendation_request':
                continue  # Already checked above
            for pattern in data['patterns']:
                if re.search(pattern, message):
                    return intent
        return 'general_query'
    
    def _extract_entities(self, message: str) -> Dict[str, str]:
        """Extract entities like gender, season, clothing type, etc."""
        entities = {}
        
        for entity_type, values in self.entities.items():
            for value, patterns in values.items():
                for pattern in patterns:
                    if re.search(pattern, message):
                        entities[entity_type] = value
                        break
                if entity_type in entities:
                    break
        
        return entities
    
    def _generate_response(self, intent: str, entities: Dict[str, str]) -> str:
        """Generate appropriate response based on intent and entities"""
        if intent in self.intents:
            base_response = random.choice(self.intents[intent]['responses'])
        else:
            base_response = "I understand you're looking for clothing recommendations. Let me help you find something great!"
        
        # Customize response based on entities
        if entities.get('gender'):
            base_response += f" I'll focus on {entities['gender']}'s clothing."
        if entities.get('season'):
            base_response += f" Perfect for {entities['season']} weather!"
        if entities.get('clothing_type'):
            base_response += f" I'll look for great {entities['clothing_type']} options."
        
        return base_response
    
    def _calculate_confidence(self, intent: str, entities: Dict[str, str]) -> float:
        """Calculate confidence score for the understanding"""
        base_confidence = 0.7 if intent != 'general_query' else 0.5
        entity_bonus = len(entities) * 0.1
        return min(1.0, base_confidence + entity_bonus)

class RecommendationEngine:
    """Enhanced AI recommendation engine with ML capabilities"""
    
    def __init__(self):
        self.model_version = "ml-v1.0"
        
        # Initialize ML model if available
        if ML_AVAILABLE:
            self.cf_model = SimpleCollaborativeFiltering()
            self.is_ml_enabled = self.cf_model.load_model()
            
            if self.is_ml_enabled:
                logger.info("ML model loaded successfully")
            else:
                logger.info("ML model not available, using fallback logic")
        else:
            self.cf_model = None
            self.is_ml_enabled = False
            logger.info("ML not available - using mock recommendations only")
    
    def get_recommendations(self, params: Dict[str, Any]) -> Dict[str, Any]:
        """Generate product recommendations using ML or fallback"""
        start_time = time.time()
        
        user_id = params.get('user_id')
        gender_context = params.get('gender_context', 'detect')
        season = params.get('season', 'auto')
        user_signals = params.get('user_signals', {})
        topk = params.get('topk', 8)
        
        # Try ML recommendations first
        if self.is_ml_enabled and user_id:
            try:
                ml_recommendations = self.cf_model.get_recommendations(user_id, topk)
                recommendations = self._format_ml_recommendations(ml_recommendations, gender_context, season)
                method_used = "ml_collaborative_filtering"
            except Exception as e:
                logger.error(f"ML recommendation failed: {str(e)}")
                recommendations = self._get_mock_recommendations(gender_context, season, user_signals, topk)
                method_used = "fallback_after_ml_error"
        else:
            # Use existing mock logic
            recommendations = self._get_mock_recommendations(gender_context, season, user_signals, topk)
            method_used = "mock_logic"
        
        end_time = time.time()
        latency_ms = int((end_time - start_time) * 1000)
        
        return {
            "products": recommendations,
            "model_version": self.model_version,
            "latency_ms": latency_ms,
            "ml_enabled": self.is_ml_enabled,
            "method_used": method_used
        }
    
    def _detect_current_season(self) -> str:
        """Detect current season based on date"""
        import datetime
        month = datetime.datetime.now().month
        
        if 3 <= month <= 5:
            return 'spring'
        elif 6 <= month <= 8:
            return 'summer'
        elif 9 <= month <= 11:
            return 'autumn'
        else:
            return 'winter'
    
    def _filter_products(self, gender_context: str, season: str, user_signals: Dict) -> List[Dict]:
        """Filter products based on gender and season"""
        filtered = []
        
        for product in PRODUCTS:  # Use real products instead of MOCK_PRODUCTS
            # Gender filtering
            if gender_context != 'detect' and gender_context != 'unisex':
                if product['gender_target'] != gender_context and product['gender_target'] != 'unisex':
                    continue
            
            # Season filtering
            if season != 'all':
                if product['season'] != season and product['season'] != 'all':
                    continue
            
            filtered.append(product.copy())
        
        return filtered
    
    def _score_products(self, products: List[Dict], user_signals: Dict) -> List[Dict]:
        """Score products based on various factors"""
        for i, product in enumerate(products):
            score = 0.0
            
            # Base rating score (0.3 weight)
            score += (product['rating_avg'] / 5.0) * 0.3
            
            # Price attractiveness (0.2 weight) - prefer mid-range prices
            price_score = 1.0 - abs(product['price'] - 50) / 100
            score += max(0, price_score) * 0.2
            
            # User interaction signals (0.3 weight)
            viewed = user_signals.get('viewed', [])
            wishlisted = user_signals.get('wishlisted', [])
            carted = user_signals.get('carted', [])
            
            if product['id'] in viewed:
                score += 0.1
            if product['id'] in wishlisted:
                score += 0.15
            if product['id'] in carted:
                score += 0.05
            
            # Diversity factor - boost different product types (0.2 weight)
            diversity_boost = (i * 0.05) + random.random() * 0.15  # Different boost for each position
            score += diversity_boost
            
            product['score'] = min(1.0, score)
        
        # Sort by score descending
        return sorted(products, key=lambda x: x['score'], reverse=True)
    
    def _generate_reason(self, product: Dict, gender_context: str, season: str) -> str:
        """Generate explanation for why this product is recommended"""
        templates = [
            f"Perfect {product['name'].lower()} for {gender_context} in {season}",
            f"Highly rated {product['name'].lower()} ideal for {season} weather",
            f"Popular {product['name'].lower()} great for {gender_context}",
            f"Trending {product['name'].lower()} perfect for the season",
            f"Quality {product['name'].lower()} with excellent reviews"
        ]
        
        # Choose template based on product characteristics
        if product['rating'] >= 4.5:
            reason = templates[1]  # Highly rated
        elif product['price'] < 30:
            reason = templates[2]  # Popular/affordable
        else:
            reason = random.choice(templates)
        
        # Ensure reason is under 22 words
        words = reason.split()
        if len(words) > 22:
            reason = ' '.join(words[:22])
        
        return reason.capitalize()
    
    def _format_ml_recommendations(self, ml_recs, gender_context, season):
        """Format ML recommendations with product details and reasons"""
        recommendations = []
        for rec in ml_recs:
            product_id = rec['product_id']
            product = next((p for p in PRODUCTS if p['id'] == product_id), None)
            
            if product:
                reason = self._generate_reason(product, gender_context, season)
                recommendations.append({
                    "product_id": product_id,
                    "score": round(rec['score'], 2),
                    "reason": reason
                })
        return recommendations
    
    def _get_mock_recommendations(self, gender_context, season, user_signals, topk):
        """Your existing mock recommendation logic"""
        # Auto-detect season if needed
        if season == 'auto':
            season = self._detect_current_season()
        
        # Filter products based on criteria
        filtered_products = self._filter_products(gender_context, season, user_signals)
        
        # Score and rank products
        scored_products = self._score_products(filtered_products, user_signals)
        
        # Select top-k products
        top_products = scored_products[:topk]
        
        # Generate recommendations with reasons
        recommendations = []
        for product in top_products:
            reason = self._generate_reason(product, gender_context, season)
            recommendations.append({
                "product_id": product["id"],
                "score": round(product["score"], 2),
                "reason": reason
            })
        
        return recommendations

# Initialize engines
rec_engine = RecommendationEngine()
nlu_engine = NLUEngine()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        "status": "healthy",
        "service": "ai-recommendation-service",
        "version": "1.0.0",
        "model_version": rec_engine.model_version
    })

@app.route('/recommend', methods=['POST'])
def recommend():
    """Get product recommendations"""
    try:
        data = request.get_json()
        
        if not data:
            return jsonify({"error": "No JSON data provided"}), 400
        
        # Validate required parameters
        valid_genders = ['men', 'women', 'kids', 'unisex', 'detect']
        valid_seasons = ['spring', 'summer', 'autumn', 'winter', 'all', 'auto']
        
        gender_context = data.get('gender_context', 'detect')
        season = data.get('season', 'auto')
        topk = data.get('topk', 8)
        
        if gender_context not in valid_genders:
            return jsonify({"error": f"Invalid gender_context. Must be one of: {valid_genders}"}), 400
        
        if season not in valid_seasons:
            return jsonify({"error": f"Invalid season. Must be one of: {valid_seasons}"}), 400
        
        if not isinstance(topk, int) or topk < 1 or topk > 20:
            return jsonify({"error": "topk must be an integer between 1 and 20"}), 400
        
        # Get recommendations
        result = rec_engine.get_recommendations(data)
        
        logger.info(f"Generated {len(result['products'])} recommendations in {result['latency_ms']}ms")
        
        return jsonify(result)
    
    except Exception as e:
        logger.error(f"Error generating recommendations: {str(e)}")
        return jsonify({"error": "Internal server error"}), 500

@app.route('/explain/<int:product_id>', methods=['GET'])
def explain_product(product_id: int):
    """Get explanation for a specific product recommendation"""
    try:
        # Find product
        product = next((p for p in PRODUCTS if p['id'] == product_id), None)  # Use real products
        
        if not product:
            return jsonify({"error": "Product not found"}), 404
        
        # Generate explanation
        features = [
            {"key": "rating", "weight": product['rating'] / 5.0},
            {"key": "price_attractiveness", "weight": max(0, 1.0 - abs(product['price'] - 50) / 100)},
            {"key": "seasonal_match", "weight": 0.8},
            {"key": "category_preference", "weight": 0.7}
        ]
        
        reason = f"Recommended based on high rating ({product['rating']}/5) and seasonal appropriateness"
        
        return jsonify({
            "reason": reason,
            "features": features,
            "model_version": rec_engine.model_version
        })
    
    except Exception as e:
        logger.error(f"Error explaining product {product_id}: {str(e)}")
        return jsonify({"error": "Internal server error"}), 500

@app.route('/chat', methods=['POST'])
def chat():
    """Chat with AI assistant for personalized recommendations"""
    try:
        data = request.get_json()
        
        if not data or 'message' not in data:
            return jsonify({"error": "Message is required"}), 400
        
        user_message = data['message']
        user_context = data.get('user_context', {})
        
        # Understand the message using NLU
        nlu_result = nlu_engine.understand_message(user_message)
        
        # Check if this is a recommendation request
        if nlu_result['intent'] in ['recommendation_request', 'seasonal_query', 'price_query', 'general_query'] or 'clothing' in user_message.lower() or 'recommend' in user_message.lower():
            # Extract recommendation parameters from entities
            rec_params = {
                'gender_context': nlu_result['entities'].get('gender', 'detect'),
                'season': nlu_result['entities'].get('season', 'auto'),
                'topk': 4,  # Fewer recommendations for chat
                'user_signals': user_context.get('user_signals', {})
            }
            
            # Get recommendations
            recommendations = rec_engine.get_recommendations(rec_params)
            
            # Format response with products
            response = {
                'message': nlu_result['response'],
                'intent': nlu_result['intent'],
                'confidence': nlu_result['confidence'],
                'has_recommendations': True,
                'recommendations': recommendations['products'][:4],  # Top 4 for chat
                'entities_detected': nlu_result['entities']
            }
        else:
            # Just conversational response
            response = {
                'message': nlu_result['response'],
                'intent': nlu_result['intent'],
                'confidence': nlu_result['confidence'],
                'has_recommendations': False,
                'entities_detected': nlu_result['entities']
            }
        
        logger.info(f"Chat response generated - Intent: {nlu_result['intent']}, Confidence: {nlu_result['confidence']}")
        
        return jsonify(response)
    
    except Exception as e:
        logger.error(f"Error in chat endpoint: {str(e)}")
        return jsonify({"error": "Internal server error"}), 500

@app.route('/model/info', methods=['GET'])
def model_info():
    """Get information about the AI model"""
    return jsonify({
        "model_version": rec_engine.model_version,
        "model_type": "mock_recommendation_engine",
        "features": [
            "Gender-based filtering",
            "Seasonal recommendations", 
            "User behavior signals",
            "Rating-based scoring",
            "Diversity injection"
        ],
        "supported_categories": ["men", "women", "kids", "unisex"],
        "supported_seasons": ["spring", "summer", "autumn", "winter", "all"],
        "max_recommendations": 20
    })

@app.route('/test-ml', methods=['GET'])
def test_ml_model():
    """Test endpoint for ML model"""
    user_id = request.args.get('user_id', type=int)
    
    if not user_id:
        return jsonify({"error": "user_id parameter required"}), 400
    
    try:
        # Test ML recommendations
        recommendations = rec_engine.get_recommendations({
            'user_id': user_id,
            'topk': 5
        })
        
        return jsonify({
            "user_id": user_id,
            "ml_enabled": rec_engine.is_ml_enabled,
            "method_used": recommendations.get('method_used', 'unknown'),
            "recommendations": recommendations['products']
        })
    
    except Exception as e:
        logger.error(f"Error testing ML model: {str(e)}")
        return jsonify({"error": "Internal server error"}), 500

@app.route('/ml-info', methods=['GET'])
def ml_model_info():
    """Get information about the ML model"""
    if rec_engine.is_ml_enabled and rec_engine.cf_model:
        model_info = rec_engine.cf_model.get_model_info()
        return jsonify({
            "ml_enabled": True,
            "model_info": model_info
        })
    else:
        return jsonify({
            "ml_enabled": False,
            "message": "ML model not trained or loaded"
        })

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    debug = os.environ.get('DEBUG', 'false').lower() == 'true'
    
    logger.info(f"Starting AI Recommendation Service on port {port}")
    logger.info(f"Model version: {rec_engine.model_version}")
    
    app.run(host='0.0.0.0', port=port, debug=debug)

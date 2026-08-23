# Quick Start: Converting Mock AI to Real ML

## Immediate Action Plan

### Step 1: Add User Interaction Tracking (Week 1)

First, you need to track user behavior to have data for training. Add this to your database:

```sql
-- Add to your existing database
CREATE TABLE user_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    product_id INT NOT NULL,
    interaction_type ENUM('view', 'cart_add', 'cart_remove', 'wishlist_add', 'wishlist_remove', 'purchase') NOT NULL,
    session_id VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_interaction (user_id, interaction_type, timestamp),
    INDEX idx_product_interaction (product_id, interaction_type, timestamp)
);
```

### Step 2: Simple Collaborative Filtering (Week 2-3)

Replace your mock recommendation engine with a basic ML model:

```python
# Create: ai-service/models/simple_cf.py
import numpy as np
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.decomposition import TruncatedSVD
import joblib
import os

class SimpleCollaborativeFiltering:
    def __init__(self):
        self.user_item_matrix = None
        self.product_similarity = None
        self.user_similarity = None
        self.model_path = 'models/trained/simple_cf.pkl'
        
    def train(self, interactions_df):
        """Train collaborative filtering model"""
        print("Training Collaborative Filtering model...")
        
        # Create user-item interaction matrix
        self.user_item_matrix = interactions_df.pivot_table(
            index='user_id', 
            columns='product_id', 
            values='interaction_score', 
            fill_value=0
        )
        
        # Calculate product similarities
        self.product_similarity = cosine_similarity(self.user_item_matrix.T)
        
        # Calculate user similarities  
        self.user_similarity = cosine_similarity(self.user_item_matrix)
        
        # Save model
        os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
        joblib.dump({
            'user_item_matrix': self.user_item_matrix,
            'product_similarity': self.product_similarity,
            'user_similarity': self.user_similarity
        }, self.model_path)
        
        print(f"Model saved to {self.model_path}")
    
    def load_model(self):
        """Load trained model"""
        if os.path.exists(self.model_path):
            model_data = joblib.load(self.model_path)
            self.user_item_matrix = model_data['user_item_matrix']
            self.product_similarity = model_data['product_similarity']
            self.user_similarity = model_data['user_similarity']
            return True
        return False
    
    def get_recommendations(self, user_id, n_recommendations=8):
        """Get recommendations for a user"""
        if not self.load_model():
            return self._fallback_recommendations()
        
        try:
            # Get user's interaction history
            user_interactions = self.user_item_matrix.loc[user_id]
            interacted_products = user_interactions[user_interactions > 0].index
            
            # Find similar users
            user_idx = self.user_item_matrix.index.get_loc(user_id)
            similar_users = self.user_similarity[user_idx]
            similar_user_indices = np.argsort(similar_users)[::-1][1:11]  # Top 10 similar users
            
            # Get products liked by similar users
            recommendations = {}
            for similar_user_idx in similar_user_indices:
                similar_user_id = self.user_item_matrix.index[similar_user_idx]
                similar_user_interactions = self.user_item_matrix.iloc[similar_user_idx]
                
                for product_id, score in similar_user_interactions.items():
                    if score > 0 and product_id not in interacted_products:
                        if product_id not in recommendations:
                            recommendations[product_id] = 0
                        recommendations[product_id] += score * similar_users[similar_user_idx]
            
            # Sort by score and return top recommendations
            sorted_recommendations = sorted(recommendations.items(), key=lambda x: x[1], reverse=True)
            return [{'product_id': pid, 'score': score} for pid, score in sorted_recommendations[:n_recommendations]]
            
        except KeyError:
            # User not found in training data (cold start)
            return self._cold_start_recommendations()
    
    def _fallback_recommendations(self):
        """Fallback to mock recommendations"""
        return [{'product_id': i, 'score': 0.5} for i in range(1, 9)]
    
    def _cold_start_recommendations(self):
        """Recommendations for new users"""
        # Return most popular products
        if self.user_item_matrix is not None:
            popular_products = self.user_item_matrix.sum().sort_values(ascending=False).head(8)
            return [{'product_id': pid, 'score': score} for pid, score in popular_products.items()]
        return self._fallback_recommendations()
```

### Step 3: Enhanced RecommendationEngine (Week 3-4)

Update your existing `RecommendationEngine` class:

```python
# Replace the RecommendationEngine class in app.py
class RecommendationEngine:
    def __init__(self):
        self.model_version = "ml-v1.0"
        self.cf_model = SimpleCollaborativeFiltering()
        self.is_ml_enabled = self.cf_model.load_model()
        
    def get_recommendations(self, params: Dict[str, Any]) -> Dict[str, Any]:
        start_time = time.time()
        
        user_id = params.get('user_id')
        gender_context = params.get('gender_context', 'detect')
        season = params.get('season', 'auto')
        user_signals = params.get('user_signals', {})
        topk = params.get('topk', 8)
        
        if self.is_ml_enabled and user_id:
            # Use ML model
            ml_recommendations = self.cf_model.get_recommendations(user_id, topk)
            recommendations = self._format_ml_recommendations(ml_recommendations, gender_context, season)
        else:
            # Fallback to existing mock logic
            recommendations = self._get_mock_recommendations(gender_context, season, user_signals, topk)
        
        end_time = time.time()
        latency_ms = int((end_time - start_time) * 1000)
        
        return {
            "products": recommendations,
            "model_version": self.model_version,
            "latency_ms": latency_ms,
            "ml_enabled": self.is_ml_enabled
        }
    
    def _format_ml_recommendations(self, ml_recs, gender_context, season):
        """Format ML recommendations with product details"""
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
        """Fallback to existing mock logic"""
        # Your existing mock recommendation logic here
        filtered_products = self._filter_products(gender_context, season, user_signals)
        scored_products = self._score_products(filtered_products, user_signals)
        top_products = scored_products[:topk]
        
        recommendations = []
        for product in top_products:
            reason = self._generate_reason(product, gender_context, season)
            recommendations.append({
                "product_id": product["id"],
                "score": round(product["score"], 2),
                "reason": reason
            })
        return recommendations
```

### Step 4: Training Script (Week 4)

Create a training script to convert your existing data:

```python
# Create: ai-service/train_simple_model.py
import pandas as pd
import pymysql
from models.simple_cf import SimpleCollaborativeFiltering
import json

def load_training_data():
    """Load existing order data for training"""
    # Connect to your database
    connection = pymysql.connect(
        host='localhost',
        user='root',
        password='',  # Your DB password
        database='clothing_ecommerce'
    )
    
    # Load order items (purchases)
    orders_query = """
    SELECT oi.product_id, o.user_id, 
           COUNT(*) as purchase_count,
           SUM(oi.qty) as total_qty,
           AVG(oi.unit_price) as avg_price
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status IN ('paid', 'shipped', 'delivered')
    GROUP BY oi.product_id, o.user_id
    """
    
    orders_df = pd.read_sql(orders_query, connection)
    
    # Load wishlist data
    wishlist_query = """
    SELECT product_id, user_id, 1 as wishlist_count
    FROM wishlist_items wi
    JOIN wishlists w ON wi.wishlist_id = w.id
    """
    
    wishlist_df = pd.read_sql(wishlist_query, connection)
    
    # Load cart data (optional - if you track abandoned carts)
    cart_query = """
    SELECT ci.product_id, c.user_id, 
           COUNT(*) as cart_count,
           SUM(ci.qty) as total_cart_qty
    FROM cart_items ci
    JOIN carts c ON ci.cart_id = c.id
    GROUP BY ci.product_id, c.user_id
    """
    
    cart_df = pd.read_sql(cart_query, connection)
    connection.close()
    
    # Combine all interactions
    interactions = []
    
    # Add purchases (weight: 3.0)
    for _, row in orders_df.iterrows():
        interactions.append({
            'user_id': row['user_id'],
            'product_id': row['product_id'],
            'interaction_score': 3.0 + (row['purchase_count'] * 0.5)
        })
    
    # Add wishlist items (weight: 2.0)
    for _, row in wishlist_df.iterrows():
        interactions.append({
            'user_id': row['user_id'],
            'product_id': row['product_id'],
            'interaction_score': 2.0
        })
    
    # Add cart items (weight: 1.0)
    for _, row in cart_df.iterrows():
        interactions.append({
            'user_id': row['user_id'],
            'product_id': row['product_id'],
            'interaction_score': 1.0
        })
    
    return pd.DataFrame(interactions)

def main():
    print("Loading training data...")
    interactions_df = load_training_data()
    
    print(f"Loaded {len(interactions_df)} interactions")
    print(f"Unique users: {interactions_df['user_id'].nunique()}")
    print(f"Unique products: {interactions_df['product_id'].nunique()}")
    
    # Train model
    cf_model = SimpleCollaborativeFiltering()
    cf_model.train(interactions_df)
    
    print("Training completed!")
    
    # Test recommendations
    test_user_id = interactions_df['user_id'].iloc[0]
    recommendations = cf_model.get_recommendations(test_user_id, 5)
    print(f"Test recommendations for user {test_user_id}:")
    for rec in recommendations:
        print(f"  Product {rec['product_id']}: {rec['score']:.3f}")

if __name__ == "__main__":
    main()
```

### Step 5: Enhanced NLU (Week 5)

Improve the NLU engine with better entity extraction:

```python
# Enhanced NLUEngine class
class EnhancedNLUEngine(NLUEngine):
    def __init__(self):
        super().__init__()
        self.clothing_keywords = {
            'tops': ['shirt', 'blouse', 't-shirt', 'tee', 'top', 'tank', 'crop'],
            'bottoms': ['pants', 'jeans', 'shorts', 'skirt', 'trousers'],
            'dresses': ['dress', 'gown', 'frock', 'jumper'],
            'outerwear': ['jacket', 'coat', 'blazer', 'cardigan', 'sweater'],
            'shoes': ['shoes', 'boots', 'sneakers', 'sandals', 'heels'],
            'accessories': ['bag', 'hat', 'scarf', 'belt', 'watch']
        }
    
    def _extract_clothing_type(self, message: str) -> str:
        """Enhanced clothing type detection"""
        message_lower = message.lower()
        
        for clothing_type, keywords in self.clothing_keywords.items():
            for keyword in keywords:
                if keyword in message_lower:
                    return clothing_type
        
        return None
    
    def _extract_price_intent(self, message: str) -> Dict[str, Any]:
        """Extract price-related information"""
        message_lower = message.lower()
        price_info = {}
        
        # Extract specific price ranges
        import re
        price_patterns = [
            r'under \$?(\d+)',
            r'less than \$?(\d+)',
            r'below \$?(\d+)',
            r'budget.*\$?(\d+)',
            r'cheap.*\$?(\d+)'
        ]
        
        for pattern in price_patterns:
            match = re.search(pattern, message_lower)
            if match:
                price_info['max_price'] = float(match.group(1))
                break
        
        # Extract price quality indicators
        if any(word in message_lower for word in ['cheap', 'budget', 'affordable']):
            price_info['price_preference'] = 'budget'
        elif any(word in message_lower for word in ['expensive', 'luxury', 'premium']):
            price_info['price_preference'] = 'premium'
        
        return price_info
```

### Step 6: Run Training (Week 6)

Execute the training:

```bash
# In your ai-service directory
cd ai-service

# Install additional dependencies
pip install pandas scikit-learn joblib pymysql

# Create models directory
mkdir -p models/trained

# Run training
python train_simple_model.py
```

### Step 7: Test the ML Model

Add a test endpoint to verify the model works:

```python
# Add to app.py
@app.route('/test-ml', methods=['GET'])
def test_ml_model():
    """Test endpoint for ML model"""
    user_id = request.args.get('user_id', type=int)
    
    if not user_id:
        return jsonify({"error": "user_id parameter required"}), 400
    
    # Test ML recommendations
    recommendations = rec_engine.get_recommendations({
        'user_id': user_id,
        'topk': 5
    })
    
    return jsonify({
        "user_id": user_id,
        "ml_enabled": rec_engine.is_ml_enabled,
        "recommendations": recommendations['products']
    })
```

## Immediate Benefits

After implementing this:

1. **Real ML Recommendations**: Uses actual user behavior data
2. **Cold Start Handling**: Recommends popular items for new users
3. **Gradual Improvement**: Model gets better as you collect more data
4. **Fallback System**: Still works if ML model fails
5. **Easy Monitoring**: Can track ML vs mock performance

## Next Steps for Advanced AI

Once you have basic ML working:

1. **Add Content-Based Filtering**: Use product descriptions and features
2. **Implement Deep Learning**: Use neural collaborative filtering
3. **Real-time Learning**: Update model with new interactions
4. **Advanced NLU**: Use transformer models for better understanding
5. **Multi-objective Optimization**: Balance diversity, novelty, and accuracy

## Cost and Time Estimate

- **Time**: 4-6 weeks for basic implementation
- **Data**: Use existing order/wishlist data (no additional collection needed initially)
- **Resources**: Standard Python ML libraries (free)
- **Infrastructure**: Same as current (just enhanced Python service)

This approach gives you a working ML system quickly while maintaining your existing functionality as a fallback.

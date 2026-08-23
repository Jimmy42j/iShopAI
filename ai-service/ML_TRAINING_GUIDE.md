# AI Recommendation System Training Guide

## Current State Analysis

Your current `app.py` contains a **mock AI system** with:
- Rule-based NLU (regex patterns)
- Simple scoring algorithm (rating + price + user signals)
- Static product filtering
- No machine learning models

## What You Need to Train a Real AI

### 1. Data Requirements

#### User Behavior Data (Critical for Recommendations)
```sql
-- User interactions (from your existing schema)
- users (gender, age, preferences)
- cart_items (what users add to cart)
- wishlist_items (what users save)
- order_items (what users buy)
- product_views (track in frontend)

-- Missing tables you need to add:
CREATE TABLE user_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    product_id INT NOT NULL,
    interaction_type ENUM('view', 'click', 'add_to_cart', 'remove_from_cart', 'wishlist', 'purchase') NOT NULL,
    session_id VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user_interaction (user_id, interaction_type),
    INDEX idx_product_interaction (product_id, interaction_type),
    INDEX idx_timestamp (timestamp)
);

CREATE TABLE product_features (
    product_id INT PRIMARY KEY,
    features JSON, -- color, style, material, etc.
    embeddings BLOB, -- ML feature vectors
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

#### Training Data Sources
1. **Historical Purchase Data**: Order items with user IDs
2. **Implicit Feedback**: Views, cart additions, wishlist saves
3. **Product Features**: Categories, prices, ratings, descriptions
4. **User Demographics**: Age, gender, location
5. **Seasonal Patterns**: Time-based purchase behavior

### 2. Machine Learning Models to Implement

#### A. Recommendation Models

**1. Collaborative Filtering**
```python
# User-Item Collaborative Filtering
from surprise import SVD, Dataset, Reader
from sklearn.metrics.pairwise import cosine_similarity

class CollaborativeFilteringModel:
    def __init__(self):
        self.model = SVD(n_factors=100, n_epochs=20)
        self.user_item_matrix = None
    
    def train(self, interactions_df):
        # Train on user-item interactions
        reader = Reader(rating_scale=(0, 1))
        data = Dataset.load_from_df(interactions_df, reader)
        self.model.fit(data.build_full_trainset())
```

**2. Content-Based Filtering**
```python
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

class ContentBasedModel:
    def __init__(self):
        self.tfidf = TfidfVectorizer(max_features=1000)
        self.product_features = None
    
    def train(self, products_df):
        # Create product feature vectors from descriptions
        descriptions = products_df['description'].fillna('')
        self.product_features = self.tfidf.fit_transform(descriptions)
```

**3. Hybrid Model (Recommended)**
```python
class HybridRecommendationModel:
    def __init__(self):
        self.cf_model = CollaborativeFilteringModel()
        self.cb_model = ContentBasedModel()
        self.weights = {'collaborative': 0.6, 'content': 0.4}
    
    def get_recommendations(self, user_id, n_recommendations=10):
        # Combine collaborative and content-based recommendations
        cf_recs = self.cf_model.predict(user_id, n_recommendations)
        cb_recs = self.cb_model.predict(user_id, n_recommendations)
        
        # Weighted combination
        return self._combine_recommendations(cf_recs, cb_recs)
```

#### B. Natural Language Understanding (NLU)

**Replace regex-based NLU with:**
```python
import spacy
from transformers import pipeline

class AdvancedNLUEngine:
    def __init__(self):
        # Load pre-trained models
        self.nlp = spacy.load("en_core_web_sm")
        self.intent_classifier = pipeline("text-classification", 
                                        model="facebook/bart-large-mnli")
        self.ner_model = pipeline("ner", model="dbmdz/bert-large-cased-finetuned-conll03-english")
    
    def understand_message(self, message):
        # Intent classification
        intent = self.intent_classifier(message)
        
        # Named Entity Recognition for clothing attributes
        entities = self.ner_model(message)
        
        # Dependency parsing for complex queries
        doc = self.nlp(message)
        
        return self._process_nlu_result(intent, entities, doc)
```

### 3. Training Pipeline

#### Data Preprocessing
```python
class DataPreprocessor:
    def __init__(self):
        self.scaler = StandardScaler()
    
    def prepare_training_data(self):
        # 1. Load user interactions
        interactions = self.load_interactions()
        
        # 2. Create user-item matrix
        user_item_matrix = self.create_user_item_matrix(interactions)
        
        # 3. Feature engineering
        features = self.extract_features(interactions)
        
        # 4. Handle cold start problems
        features = self.handle_cold_start(features)
        
        return user_item_matrix, features
    
    def extract_features(self, interactions):
        features = []
        for interaction in interactions:
            feature_vector = {
                'user_age': self.get_user_age(interaction['user_id']),
                'user_gender': self.encode_gender(interaction['user_id']),
                'product_price': interaction['price'],
                'product_rating': interaction['rating'],
                'season': self.encode_season(interaction['timestamp']),
                'time_of_day': interaction['timestamp'].hour,
                'day_of_week': interaction['timestamp'].weekday(),
                'interaction_strength': self.calculate_interaction_strength(interaction)
            }
            features.append(feature_vector)
        return features
```

#### Model Training
```python
class ModelTrainer:
    def __init__(self):
        self.models = {}
    
    def train_all_models(self):
        # 1. Train collaborative filtering
        self.train_collaborative_filtering()
        
        # 2. Train content-based model
        self.train_content_based()
        
        # 3. Train hybrid model
        self.train_hybrid_model()
        
        # 4. Train NLU models
        self.train_nlu_models()
        
        # 5. Evaluate models
        self.evaluate_models()
    
    def train_collaborative_filtering(self):
        # Split data for training/validation
        train_data, val_data = self.split_data(self.interactions)
        
        # Train SVD model
        self.models['collaborative'] = SVD(n_factors=100)
        self.models['collaborative'].fit(train_data)
        
        # Validate
        rmse = self.evaluate_cf_model(val_data)
        print(f"Collaborative Filtering RMSE: {rmse}")
```

### 4. Integration with Existing System

#### Enhanced AI Service Structure
```
ai-service/
├── app.py (main Flask app)
├── models/
│   ├── __init__.py
│   ├── collaborative_filtering.py
│   ├── content_based.py
│   ├── hybrid_model.py
│   └── nlu_engine.py
├── data/
│   ├── __init__.py
│   ├── preprocessor.py
│   ├── feature_extractor.py
│   └── data_loader.py
├── training/
│   ├── __init__.py
│   ├── trainer.py
│   ├── evaluator.py
│   └── pipeline.py
├── config/
│   ├── model_config.yaml
│   └── training_config.yaml
├── utils/
│   ├── __init__.py
│   ├── metrics.py
│   └── helpers.py
├── requirements.txt
├── train_models.py
└── ML_TRAINING_GUIDE.md
```

#### Training Script
```python
# train_models.py
import yaml
from training.trainer import ModelTrainer
from data.data_loader import DataLoader

def main():
    # Load configuration
    with open('config/training_config.yaml', 'r') as f:
        config = yaml.safe_load(f)
    
    # Load data
    data_loader = DataLoader(config['database'])
    interactions, products, users = data_loader.load_all_data()
    
    # Train models
    trainer = ModelTrainer(config['models'])
    trainer.train_all_models(interactions, products, users)
    
    # Save trained models
    trainer.save_models(config['model_path'])

if __name__ == "__main__":
    main()
```

### 5. Deployment Strategy

#### Model Serving
```python
# Enhanced app.py with ML models
from models.hybrid_model import HybridRecommendationModel
from models.nlu_engine import AdvancedNLUEngine

class MLRecommendationService:
    def __init__(self):
        self.recommendation_model = HybridRecommendationModel()
        self.nlu_engine = AdvancedNLUEngine()
        self.load_trained_models()
    
    def load_trained_models(self):
        # Load pre-trained models
        self.recommendation_model.load('models/trained/hybrid_model.pkl')
        self.nlu_engine.load('models/trained/nlu_model.pkl')
    
    def get_recommendations(self, user_id, context):
        # Use ML models instead of mock scoring
        return self.recommendation_model.predict(user_id, context)
```

### 6. Required Dependencies

```txt
# requirements.txt (enhanced)
flask==2.3.3
flask-cors==4.0.0
numpy==1.24.3
pandas==2.0.3
scikit-learn==1.3.0
surprise==1.1.3
transformers==4.32.0
torch==2.0.1
spacy==3.6.1
pymysql==1.1.0
sqlalchemy==2.0.19
joblib==1.3.2
pyyaml==6.0.1
matplotlib==3.7.2
seaborn==0.12.2
```

### 7. Training Schedule

#### Phase 1: Data Collection (2-4 weeks)
- Add user interaction tracking to frontend
- Implement data collection endpoints
- Set up data pipeline

#### Phase 2: Model Development (4-6 weeks)
- Implement collaborative filtering
- Implement content-based filtering
- Develop hybrid model
- Train and validate models

#### Phase 3: NLU Enhancement (2-3 weeks)
- Replace regex-based NLU with transformer models
- Train intent classification
- Implement entity recognition

#### Phase 4: Integration & Testing (2-3 weeks)
- Integrate ML models into existing service
- A/B testing with current mock system
- Performance optimization

#### Phase 5: Deployment (1-2 weeks)
- Deploy trained models to production
- Monitor model performance
- Continuous retraining pipeline

### 8. Monitoring & Maintenance

```python
class ModelMonitor:
    def __init__(self):
        self.metrics_collector = MetricsCollector()
    
    def monitor_performance(self):
        # Track recommendation accuracy
        accuracy = self.calculate_accuracy()
        
        # Track user engagement
        engagement = self.calculate_engagement()
        
        # Track model drift
        drift = self.detect_model_drift()
        
        if drift > threshold:
            self.trigger_retraining()
```

## Next Steps

1. **Start with data collection** - Add interaction tracking
2. **Implement basic collaborative filtering** - Start with user-item matrix
3. **Add content-based features** - Use product descriptions
4. **Develop hybrid model** - Combine both approaches
5. **Enhance NLU** - Replace regex with transformer models
6. **Set up training pipeline** - Automated model training
7. **Implement monitoring** - Track model performance

The key is to start simple and gradually add complexity. Begin with collaborative filtering on your existing order data, then expand to more sophisticated models as you collect more user interaction data.

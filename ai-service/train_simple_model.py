#!/usr/bin/env python3
"""
Training Script for Simple Collaborative Filtering Model
Converts existing order/wishlist data into ML training data
"""

import pandas as pd
import pymysql
import os
import sys
import logging
from typing import Optional

# Add current directory to path for imports
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from models.simple_cf import SimpleCollaborativeFiltering

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class DataLoader:
    """Load training data from the database"""
    
    def __init__(self, db_config: dict):
        self.db_config = db_config
        self.connection = None
    
    def connect(self):
        """Connect to database"""
        try:
            self.connection = pymysql.connect(**self.db_config)
            logger.info("Connected to database")
            return True
        except Exception as e:
            logger.error(f"Database connection failed: {str(e)}")
            return False
    
    def disconnect(self):
        """Disconnect from database"""
        if self.connection:
            self.connection.close()
            self.connection = None
    
    def load_interactions(self) -> pd.DataFrame:
        """Load all user interactions for training"""
        interactions = []
        
        try:
            # Load purchase data (highest weight)
            logger.info("Loading purchase data...")
            purchase_query = """
            SELECT oi.product_id, o.user_id, 
                   COUNT(*) as purchase_count,
                   SUM(oi.qty) as total_qty
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status IN ('paid', 'shipped', 'delivered')
            GROUP BY oi.product_id, o.user_id
            """
            
            purchase_df = pd.read_sql(purchase_query, self.connection)
            logger.info(f"Loaded {len(purchase_df)} purchase interactions")
            
            # Add purchases with high weight
            for _, row in purchase_df.iterrows():
                # Weight based on quantity and frequency
                weight = 3.0 + (row['purchase_count'] * 0.5) + (row['total_qty'] * 0.2)
                interactions.append({
                    'user_id': row['user_id'],
                    'product_id': row['product_id'],
                    'interaction_score': min(weight, 10.0)  # Cap at 10
                })
            
            # Load wishlist data
            logger.info("Loading wishlist data...")
            wishlist_query = """
            SELECT wi.product_id, w.user_id
            FROM wishlist_items wi
            JOIN wishlists w ON wi.wishlist_id = w.id
            """
            
            wishlist_df = pd.read_sql(wishlist_query, self.connection)
            logger.info(f"Loaded {len(wishlist_df)} wishlist interactions")
            
            # Add wishlist items
            for _, row in wishlist_df.iterrows():
                interactions.append({
                    'user_id': row['user_id'],
                    'product_id': row['product_id'],
                    'interaction_score': 2.0
                })
            
            # Load cart data (lower weight)
            logger.info("Loading cart data...")
            cart_query = """
            SELECT ci.product_id, c.user_id, 
                   COUNT(*) as cart_count,
                   SUM(ci.qty) as total_cart_qty
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            WHERE c.user_id IS NOT NULL
            GROUP BY ci.product_id, c.user_id
            """
            
            cart_df = pd.read_sql(cart_query, self.connection)
            logger.info(f"Loaded {len(cart_df)} cart interactions")
            
            # Add cart items
            for _, row in cart_df.iterrows():
                weight = 1.0 + (row['cart_count'] * 0.3) + (row['total_cart_qty'] * 0.1)
                interactions.append({
                    'user_id': row['user_id'],
                    'product_id': row['product_id'],
                    'interaction_score': min(weight, 3.0)  # Cap at 3
                })
            
        except Exception as e:
            logger.error(f"Error loading interactions: {str(e)}")
            return pd.DataFrame()
        
        interactions_df = pd.DataFrame(interactions)
        logger.info(f"Total interactions loaded: {len(interactions_df)}")
        
        return interactions_df
    
    def get_data_stats(self, interactions_df: pd.DataFrame) -> dict:
        """Get statistics about the training data"""
        if interactions_df.empty:
            return {}
        
        return {
            'total_interactions': len(interactions_df),
            'unique_users': interactions_df['user_id'].nunique(),
            'unique_products': interactions_df['product_id'].nunique(),
            'avg_interactions_per_user': interactions_df.groupby('user_id').size().mean(),
            'avg_interactions_per_product': interactions_df.groupby('product_id').size().mean(),
            'interaction_score_range': {
                'min': interactions_df['interaction_score'].min(),
                'max': interactions_df['interaction_score'].max(),
                'mean': interactions_df['interaction_score'].mean()
            }
        }

def main():
    """Main training function"""
    logger.info("Starting ML model training...")
    
    # Database configuration
    db_config = {
        'host': os.getenv('DB_HOST', 'localhost'),
        'user': os.getenv('DB_USER', 'root'),
        'password': os.getenv('DB_PASS', ''),
        'database': os.getenv('DB_NAME', 'clothing_ecommerce'),
        'port': int(os.getenv('DB_PORT', 3306)),
        'charset': 'utf8mb4'
    }
    
    # Initialize data loader
    data_loader = DataLoader(db_config)
    
    if not data_loader.connect():
        logger.error("Failed to connect to database")
        return False
    
    try:
        # Load training data
        interactions_df = data_loader.load_interactions()
        
        if interactions_df.empty:
            logger.error("No training data found")
            return False
        
        # Print data statistics
        stats = data_loader.get_data_stats(interactions_df)
        logger.info("Training data statistics:")
        for key, value in stats.items():
            logger.info(f"  {key}: {value}")
        
        # Check if we have enough data
        if stats.get('unique_users', 0) < 10:
            logger.warning("Very few users in training data. Model may not perform well.")
        
        if stats.get('unique_products', 0) < 20:
            logger.warning("Very few products in training data. Model may not perform well.")
        
        # Train model
        cf_model = SimpleCollaborativeFiltering()
        success = cf_model.train(interactions_df)
        
        if not success:
            logger.error("Model training failed")
            return False
        
        # Test the model
        logger.info("Testing trained model...")
        test_user_id = interactions_df['user_id'].iloc[0]
        recommendations = cf_model.get_recommendations(test_user_id, 5)
        
        logger.info(f"Test recommendations for user {test_user_id}:")
        for rec in recommendations:
            logger.info(f"  Product {rec['product_id']}: {rec['score']:.3f} ({rec['method']})")
        
        # Get model info
        model_info = cf_model.get_model_info()
        logger.info("Trained model info:")
        for key, value in model_info.items():
            logger.info(f"  {key}: {value}")
        
        logger.info("Training completed successfully!")
        return True
        
    except Exception as e:
        logger.error(f"Training failed: {str(e)}")
        return False
    
    finally:
        data_loader.disconnect()

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)

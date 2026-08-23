#!/usr/bin/env python3
"""
Simple Collaborative Filtering Model
A basic ML model to replace the mock recommendation engine
"""

import numpy as np
import pandas as pd
from sklearn.metrics.pairwise import cosine_similarity
import joblib
import os
import logging
from typing import Dict, List, Any

logger = logging.getLogger(__name__)

class SimpleCollaborativeFiltering:
    """Simple collaborative filtering recommendation model"""
    
    def __init__(self, model_path='models/trained/simple_cf.pkl'):
        self.user_item_matrix = None
        self.product_similarity = None
        self.user_similarity = None
        self.model_path = model_path
        self.is_trained = False
        
    def train(self, interactions_df: pd.DataFrame) -> bool:
        """Train collaborative filtering model"""
        try:
            logger.info("Training Collaborative Filtering model...")
            
            if interactions_df.empty:
                logger.warning("No interaction data provided")
                return False
            
            # Create user-item interaction matrix
            self.user_item_matrix = interactions_df.pivot_table(
                index='user_id', 
                columns='product_id', 
                values='interaction_score', 
                fill_value=0,
                aggfunc='sum'  # Sum multiple interactions
            )
            
            logger.info(f"Created user-item matrix: {self.user_item_matrix.shape}")
            
            # Calculate product similarities
            self.product_similarity = cosine_similarity(self.user_item_matrix.T)
            
            # Calculate user similarities  
            self.user_similarity = cosine_similarity(self.user_item_matrix)
            
            # Save model
            self._save_model()
            self.is_trained = True
            
            logger.info(f"Model trained and saved to {self.model_path}")
            return True
            
        except Exception as e:
            logger.error(f"Error training model: {str(e)}")
            return False
    
    def load_model(self) -> bool:
        """Load trained model from disk"""
        try:
            if os.path.exists(self.model_path):
                model_data = joblib.load(self.model_path)
                self.user_item_matrix = model_data['user_item_matrix']
                self.product_similarity = model_data['product_similarity']
                self.user_similarity = model_data['user_similarity']
                self.is_trained = True
                logger.info("Model loaded successfully")
                return True
            else:
                logger.warning(f"Model file not found: {self.model_path}")
                return False
        except Exception as e:
            logger.error(f"Error loading model: {str(e)}")
            return False
    
    def get_recommendations(self, user_id: int, n_recommendations: int = 8, 
                          exclude_products: List[int] = None) -> List[Dict[str, Any]]:
        """Get recommendations for a user"""
        if not self.is_trained:
            logger.warning("Model not trained, returning fallback recommendations")
            return self._fallback_recommendations(n_recommendations)
        
        try:
            # Check if user exists in training data
            if user_id not in self.user_item_matrix.index:
                logger.info(f"User {user_id} not in training data (cold start)")
                return self._cold_start_recommendations(n_recommendations, exclude_products)
            
            # Get user's interaction history
            user_interactions = self.user_item_matrix.loc[user_id]
            interacted_products = set(user_interactions[user_interactions > 0].index)
            
            # Add excluded products
            if exclude_products:
                interacted_products.update(exclude_products)
            
            # Find similar users
            user_idx = self.user_item_matrix.index.get_loc(user_id)
            similar_users = self.user_similarity[user_idx]
            
            # Get top similar users (excluding self)
            similar_user_indices = np.argsort(similar_users)[::-1][1:11]
            
            # Get products liked by similar users
            recommendations = {}
            for similar_user_idx in similar_user_indices:
                similarity_score = similar_users[similar_user_idx]
                similar_user_id = self.user_item_matrix.index[similar_user_idx]
                similar_user_interactions = self.user_item_matrix.iloc[similar_user_idx]
                
                # Weight recommendations by user similarity
                for product_id, score in similar_user_interactions.items():
                    if score > 0 and product_id not in interacted_products:
                        if product_id not in recommendations:
                            recommendations[product_id] = 0
                        recommendations[product_id] += score * similarity_score
            
            # Sort by score and return top recommendations
            sorted_recommendations = sorted(recommendations.items(), 
                                          key=lambda x: x[1], reverse=True)
            
            result = []
            for product_id, score in sorted_recommendations[:n_recommendations]:
                result.append({
                    'product_id': int(product_id),
                    'score': float(score),
                    'method': 'collaborative_filtering'
                })
            
            logger.info(f"Generated {len(result)} recommendations for user {user_id}")
            return result
            
        except Exception as e:
            logger.error(f"Error generating recommendations for user {user_id}: {str(e)}")
            return self._fallback_recommendations(n_recommendations)
    
    def _cold_start_recommendations(self, n_recommendations: int, 
                                  exclude_products: List[int] = None) -> List[Dict[str, Any]]:
        """Recommendations for new users (cold start problem)"""
        try:
            # Return most popular products
            popular_products = self.user_item_matrix.sum().sort_values(ascending=False)
            
            result = []
            count = 0
            for product_id, score in popular_products.items():
                if exclude_products and product_id in exclude_products:
                    continue
                    
                result.append({
                    'product_id': int(product_id),
                    'score': float(score),
                    'method': 'popularity_based'
                })
                count += 1
                
                if count >= n_recommendations:
                    break
            
            logger.info(f"Generated {len(result)} cold-start recommendations")
            return result
            
        except Exception as e:
            logger.error(f"Error generating cold-start recommendations: {str(e)}")
            return self._fallback_recommendations(n_recommendations)
    
    def _fallback_recommendations(self, n_recommendations: int) -> List[Dict[str, Any]]:
        """Fallback recommendations when model fails"""
        logger.warning("Using fallback recommendations")
        return [
            {
                'product_id': i,
                'score': 0.5,
                'method': 'fallback'
            } for i in range(1, n_recommendations + 1)
        ]
    
    def _save_model(self) -> None:
        """Save trained model to disk"""
        try:
            os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
            
            model_data = {
                'user_item_matrix': self.user_item_matrix,
                'product_similarity': self.product_similarity,
                'user_similarity': self.user_similarity,
                'model_version': '1.0.0'
            }
            
            joblib.dump(model_data, self.model_path)
            logger.info(f"Model saved to {self.model_path}")
            
        except Exception as e:
            logger.error(f"Error saving model: {str(e)}")
    
    def get_model_info(self) -> Dict[str, Any]:
        """Get information about the trained model"""
        if not self.is_trained:
            return {
                'is_trained': False,
                'model_version': '1.0.0',
                'user_count': 0,
                'product_count': 0
            }
        
        return {
            'is_trained': True,
            'model_version': '1.0.0',
            'user_count': len(self.user_item_matrix.index),
            'product_count': len(self.user_item_matrix.columns),
            'interactions_count': int(self.user_item_matrix.sum().sum()),
            'model_path': self.model_path
        }
    
    def predict_user_rating(self, user_id: int, product_id: int) -> float:
        """Predict rating for a user-item pair"""
        if not self.is_trained:
            return 0.0
        
        try:
            if user_id not in self.user_item_matrix.index:
                return 0.0
            
            if product_id not in self.user_item_matrix.columns:
                return 0.0
            
            # Get user's average rating
            user_ratings = self.user_item_matrix.loc[user_id]
            user_avg = user_ratings[user_ratings > 0].mean()
            
            if pd.isna(user_avg):
                return 0.0
            
            # Find similar products
            if product_id in self.user_item_matrix.columns:
                product_idx = self.user_item_matrix.columns.get_loc(product_id)
                similar_products = self.product_similarity[product_idx]
                
                # Get top similar products
                similar_indices = np.argsort(similar_products)[::-1][1:6]  # Top 5
                
                # Calculate weighted prediction
                weighted_sum = 0.0
                similarity_sum = 0.0
                
                for idx in similar_indices:
                    similar_product_id = self.user_item_matrix.columns[idx]
                    similarity = similar_products[idx]
                    user_rating = self.user_item_matrix.loc[user_id, similar_product_id]
                    
                    if user_rating > 0:
                        weighted_sum += user_rating * similarity
                        similarity_sum += similarity
                
                if similarity_sum > 0:
                    predicted_rating = weighted_sum / similarity_sum
                    return float(predicted_rating)
            
            return float(user_avg)
            
        except Exception as e:
            logger.error(f"Error predicting rating: {str(e)}")
            return 0.0

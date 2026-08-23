#!/usr/bin/env python3
"""
Simple test script for the chat endpoint
"""

import requests
import json

def test_chat():
    url = "http://localhost:5000/chat"
    data = {
        "message": "recommend me men clothing"
    }
    
    try:
        response = requests.post(url, json=data)
        print(f"Status Code: {response.status_code}")
        print(f"Response: {response.text}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"Message: {result.get('message', 'No message')}")
            print(f"Intent: {result.get('intent', 'No intent')}")
            print(f"Has Recommendations: {result.get('has_recommendations', False)}")
            if result.get('has_recommendations'):
                print(f"Recommendations: {len(result.get('recommendations', []))}")
        else:
            print(f"Error: {response.text}")
            
    except Exception as e:
        print(f"Request failed: {str(e)}")

if __name__ == "__main__":
    test_chat()

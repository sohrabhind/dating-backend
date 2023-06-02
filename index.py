# Import libraries
import json
import requests
import time

# defining key/request url
key = "https://api.binance.com/api/v3/ticker/price?symbol=KEYUSDT"
  
# requesting data from url
def executeSomething():
    data = requests.get(key)  
    data = data.json()
    print(data['price'])
    time.sleep(30)

while True:
    executeSomething()